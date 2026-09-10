<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\BranchProductPrice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseService
{
    public function __construct(private StockService $stock) {}

    /**
     * @param  list<array{product_id:int, qty:int, unit_cost:string|float|int}>  $lines
     */
    public function create(array $payload, array $lines, User $user, ?UploadedFile $scan = null): Purchase
    {
        if ($lines === []) {
            throw new RuntimeException('Add at least one purchased spare part.');
        }

        $branchId = (int) ($payload['branch_id'] ?? $user->branch_id);
        if (! $branchId) {
            throw new RuntimeException('Choose a branch for this purchase.');
        }

        return DB::transaction(function () use ($payload, $lines, $user, $scan, $branchId) {
            $path = null;
            if ($scan) {
                $path = $scan->store('purchase-invoices', 'public');
            }

            $purchase = Purchase::query()->create([
                'branch_id' => $branchId,
                'created_by' => $user->id,
                'supplier_name' => $payload['supplier_name'] ?? null,
                'invoice_number' => $payload['invoice_number'] ?? null,
                'purchased_on' => $payload['purchased_on'] ?? now()->toDateString(),
                'notes' => $payload['notes'] ?? null,
                'invoice_path' => $path,
            ]);

            $total = 0;
            foreach ($lines as $line) {
                $qty = (int) $line['qty'];
                $cost = Money::toFils($line['unit_cost']);
                if ($qty <= 0 || $cost < 0) {
                    throw new RuntimeException('Purchase qty and cost must be valid.');
                }
                $lineTotal = $cost * $qty;
                $product = Product::query()->whereKey($line['product_id'])->lockForUpdate()->firstOrFail();

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_cost_fils' => $cost,
                    'line_total_fils' => $lineTotal,
                ]);

                $this->stock->move(
                    $product,
                    StockMovementType::Receive,
                    $qty,
                    $user,
                    'Purchase '.$purchase->id,
                    lock: false,
                );

                $price = BranchProductPrice::forProduct($branchId, $product->id);
                $price->last_cost_fils = $cost;
                if (! $price->exists) {
                    $price->profit_percent = (float) ($payload['default_profit_percent'] ?? 0);
                }
                $price->recalculateFloor();
                $price->save();

                $total += $lineTotal;
            }

            $purchase->total_fils = $total;
            $purchase->save();

            return $purchase->fresh(['items.product', 'branch']);
        });
    }
}
