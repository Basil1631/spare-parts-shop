<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
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
                'status' => PurchaseStatus::AwaitingGodown,
            ]);

            $total = 0;
            foreach ($lines as $line) {
                $qty = (int) $line['qty'];
                $cost = Money::toFils($line['unit_cost']);
                if ($qty <= 0 || $cost < 0) {
                    throw new RuntimeException('Purchase qty and cost must be valid.');
                }
                $lineTotal = $cost * $qty;

                $purchase->items()->create([
                    'product_id' => $line['product_id'],
                    'qty' => $qty,
                    'received_qty' => 0,
                    'unit_cost_fils' => $cost,
                    'line_total_fils' => $lineTotal,
                ]);

                $total += $lineTotal;
            }

            $purchase->total_fils = $total;
            $purchase->save();

            return $purchase->fresh(['items.product', 'branch']);
        });
    }

    /**
     * @param  array<int, int>  $receivedByItemId
     */
    public function confirmReceipt(Purchase $purchase, array $receivedByItemId, User $user): Purchase
    {
        if (! $purchase->isAwaitingGodown()) {
            throw new RuntimeException('This purchase is already received at the godown.');
        }

        $receivedByItemId = collect($receivedByItemId)
            ->mapWithKeys(fn ($qty, $id) => [(int) $id => (int) $qty])
            ->all();

        return DB::transaction(function () use ($purchase, $receivedByItemId, $user) {
            $purchase = Purchase::query()->whereKey($purchase->id)->lockForUpdate()->with('items.product')->firstOrFail();
            $total = 0;

            foreach ($purchase->items as $item) {
                if (! array_key_exists($item->id, $receivedByItemId)) {
                    throw new RuntimeException('Enter received quantity for every line.');
                }
                $received = (int) $receivedByItemId[$item->id];
                if ($received < 0) {
                    throw new RuntimeException('Received quantity cannot be negative.');
                }
                $item->received_qty = $received;
                $item->line_total_fils = $item->unit_cost_fils * $received;
                $item->save();
                $total += $item->line_total_fils;

                if ($received > 0) {
                    $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                    $this->stock->move(
                        $product,
                        StockMovementType::Receive,
                        $received,
                        $user,
                        'Godown receipt '.$purchase->id,
                        lock: false,
                    );

                    $price = BranchProductPrice::forProduct((int) $purchase->branch_id, $product->id);
                    $price->last_cost_fils = $item->unit_cost_fils;
                    $price->recalculateFloor();
                    $price->save();
                }
            }

            $purchase->total_fils = $total;
            $purchase->status = PurchaseStatus::Received;
            $purchase->received_by = $user->id;
            $purchase->received_at = now();
            $purchase->save();

            return $purchase->fresh(['items.product', 'branch', 'receiver']);
        });
    }

    public function markPaid(Purchase $purchase, User $user): Purchase
    {
        if ($purchase->status === PurchaseStatus::Paid) {
            throw new RuntimeException('This vendor bill is already paid.');
        }
        if ($purchase->status !== PurchaseStatus::Received) {
            throw new RuntimeException('Godown must confirm receipt before the accountant can pay.');
        }

        $purchase->status = PurchaseStatus::Paid;
        $purchase->paid_by = $user->id;
        $purchase->paid_at = now();
        $purchase->save();

        return $purchase->fresh();
    }

    public function markUnpaid(Purchase $purchase): Purchase
    {
        if ($purchase->status !== PurchaseStatus::Paid) {
            throw new RuntimeException('This bill is not marked paid.');
        }

        $purchase->status = PurchaseStatus::Received;
        $purchase->paid_by = null;
        $purchase->paid_at = null;
        $purchase->save();

        return $purchase->fresh();
    }
}
