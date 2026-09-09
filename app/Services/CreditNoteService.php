<?php

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\InstallmentStatus;
use App\Enums\StockMovementType;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\CreditNote;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreditNoteService
{
    public function __construct(
        private DocumentNumberService $numbers,
        private StockService $stock,
    ) {}

    /**
     * @param  array<int,int>  $qtyByBillItemId
     */
    public function create(Bill $bill, array $qtyByBillItemId, string $reason, User $user): CreditNote
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('A reason is required for a tax credit note.');
        }

        return DB::transaction(function () use ($bill, $qtyByBillItemId, $reason, $user) {
            $bill = Bill::query()->whereKey($bill->id)->lockForUpdate()->firstOrFail();

            if ($bill->status !== BillStatus::Issued) {
                throw new RuntimeException('Cannot credit a voided bill.');
            }

            $note = CreditNote::query()->create([
                'number' => $this->numbers->nextCreditNoteNumber(),
                'bill_id' => $bill->id,
                'reason' => $reason,
                'created_by' => $user->id,
            ]);

            $subtotal = 0;
            $vatTotal = 0;
            $hasLines = false;

            foreach ($qtyByBillItemId as $billItemId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    continue;
                }

                $item = BillItem::query()
                    ->where('bill_id', $bill->id)
                    ->whereKey($billItemId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($qty > $item->returnableQty()) {
                    throw new RuntimeException("Return qty for {$item->sku} exceeds remaining billed quantity.");
                }

                $lineSubtotal = (int) round($item->line_subtotal_fils * $qty / $item->qty);
                $lineVat = (int) round($item->line_vat_fils * $qty / $item->qty);
                $lineTotal = $lineSubtotal + $lineVat;

                $note->items()->create([
                    'bill_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'qty' => $qty,
                    'line_subtotal_fils' => $lineSubtotal,
                    'line_vat_fils' => $lineVat,
                    'line_total_fils' => $lineTotal,
                ]);

                $item->returned_qty += $qty;
                $item->save();

                if ($item->product_id) {
                    $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $this->stock->move(
                            $product,
                            StockMovementType::ReturnIn,
                            $qty,
                            $user,
                            'Credit note '.$note->number,
                            $bill->id,
                            $note->id,
                            lock: false,
                        );
                    }
                }

                $subtotal += $lineSubtotal;
                $vatTotal += $lineVat;
                $hasLines = true;
            }

            if (! $hasLines) {
                throw new RuntimeException('Select at least one quantity to return.');
            }

            $total = $subtotal + $vatTotal;
            $note->subtotal_fils = $subtotal;
            $note->vat_fils = $vatTotal;
            $note->total_fils = $total;
            $note->save();

            $this->applyCreditToDues($bill, $total);
            $bill->credited_fils += $total;
            if ($bill->credited_fils > $bill->total_fils) {
                $bill->credited_fils = $bill->total_fils;
            }
            $bill->save();

            return $note->fresh(['items', 'bill']);
        });
    }

    private function applyCreditToDues(Bill $bill, int $creditFils): void
    {
        $left = $creditFils;
        $installments = $bill->installments()
            ->whereIn('status', [InstallmentStatus::Pending->value, InstallmentStatus::Partial->value])
            ->orderByDesc('sequence')
            ->lockForUpdate()
            ->get();

        foreach ($installments as $installment) {
            if ($left <= 0) {
                break;
            }
            $open = $installment->outstandingFils();
            $cut = min($open, $left);
            $installment->amount_fils -= $cut;
            if ($installment->amount_fils <= $installment->paid_fils) {
                $installment->status = $installment->paid_fils > 0
                    ? InstallmentStatus::Paid
                    : InstallmentStatus::Cancelled;
            }
            $installment->save();
            $left -= $cut;
        }
    }
}
