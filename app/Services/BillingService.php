<?php

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\InstallmentStatus;
use App\Enums\PaymentType;
use App\Enums\StockMovementType;
use App\Models\Bill;
use App\Models\Garage;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShopSetting;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BillingService
{
    public function __construct(
        private DocumentNumberService $numbers,
        private StockService $stock,
        private InstallmentScheduler $scheduler,
    ) {}

    /**
     * @param  list<array{product_id:int, qty:int, unit_price_fils?:int}>  $lines
     */
    public function create(array $payload, array $lines, User $user): Bill
    {
        if ($lines === []) {
            throw new RuntimeException('Add at least one product to the bill.');
        }

        return DB::transaction(function () use ($payload, $lines, $user) {
            $settings = ShopSetting::current();
            $paymentType = PaymentType::from($payload['payment_type']);
            $garage = ! empty($payload['garage_id'])
                ? Garage::query()->findOrFail($payload['garage_id'])
                : null;

            if ($garage === null && $paymentType !== PaymentType::Cash) {
                throw new RuntimeException('Walk-in bills must be ready cash. Choose a garage for credit or installment.');
            }

            $bill = Bill::query()->create([
                'number' => $this->numbers->nextInvoiceNumber(),
                'garage_id' => $garage?->id,
                'payment_type' => $paymentType,
                'status' => BillStatus::Issued,
                'billed_at' => now(),
                'credit_due_date' => $paymentType === PaymentType::Credit
                    ? ($payload['credit_due_date'] ?? now()->addDays((int) ($garage?->credit_days ?: 30))->toDateString())
                    : null,
                'garage_name' => $garage?->name,
                'garage_phone' => $garage?->phone,
                'garage_address' => $garage?->address,
                'garage_trn' => $garage?->trn,
                'created_by' => $user->id,
                'notes' => $payload['notes'] ?? null,
            ]);

            $subtotal = 0;
            $vatTotal = 0;

            foreach ($lines as $line) {
                $product = Product::query()->whereKey($line['product_id'])->lockForUpdate()->firstOrFail();
                $qty = (int) $line['qty'];
                if ($qty <= 0) {
                    throw new RuntimeException('Line quantity must be greater than zero.');
                }

                $unit = isset($line['unit_price_fils'])
                    ? (int) $line['unit_price_fils']
                    : $product->price_fils;
                $vatRate = $product->vatPercent((float) $settings->vat_percent);
                $lineSubtotal = $unit * $qty;
                $lineVat = Money::vatFils($lineSubtotal, $vatRate);

                $bill->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'qty' => $qty,
                    'unit_price_fils' => $unit,
                    'vat_rate' => $vatRate,
                    'line_subtotal_fils' => $lineSubtotal,
                    'line_vat_fils' => $lineVat,
                    'line_total_fils' => $lineSubtotal + $lineVat,
                ]);

                $this->stock->move(
                    $product,
                    StockMovementType::Sale,
                    $qty,
                    $user,
                    'Sale '.$bill->number,
                    $bill->id,
                    lock: false,
                );

                $subtotal += $lineSubtotal;
                $vatTotal += $lineVat;
            }

            $total = $subtotal + $vatTotal;
            $bill->subtotal_fils = $subtotal;
            $bill->vat_fils = $vatTotal;
            $bill->total_fils = $total;

            if ($paymentType === PaymentType::Cash) {
                $bill->paid_fils = $total;
                Payment::query()->create([
                    'garage_id' => $garage?->id,
                    'bill_id' => $bill->id,
                    'amount_fils' => $total,
                    'paid_at' => now(),
                    'method' => 'cash',
                    'user_id' => $user->id,
                    'note' => 'Ready cash at billing',
                ]);
            } elseif ($paymentType === PaymentType::Installment) {
                $count = max(1, (int) ($payload['installment_count'] ?? $garage?->installment_count ?? 2));
                $parts = $this->scheduler->splitAmount($total, $count);
                $dates = $this->scheduler->dueDates($bill->billed_at, $count, (int) $settings->installment_day);
                foreach ($parts as $i => $amount) {
                    Installment::query()->create([
                        'bill_id' => $bill->id,
                        'sequence' => $i + 1,
                        'amount_fils' => $amount,
                        'due_date' => $dates[$i],
                        'paid_fils' => 0,
                        'status' => InstallmentStatus::Pending,
                    ]);
                }
            }

            $bill->save();

            return $bill->fresh(['items', 'installments', 'garage']);
        });
    }

    public function void(Bill $bill, User $user, ?string $reason = null): Bill
    {
        return DB::transaction(function () use ($bill, $user, $reason) {
            $bill = Bill::query()->whereKey($bill->id)->lockForUpdate()->firstOrFail();

            if (! $bill->isIssued()) {
                throw new RuntimeException('This bill is already voided.');
            }

            if (! $bill->isSameShopDay()) {
                throw new RuntimeException('Same-day void only. Issue a tax credit note for later corrections.');
            }

            foreach ($bill->items as $item) {
                $restore = $item->qty - $item->returned_qty;
                if ($restore > 0 && $item->product_id) {
                    $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $this->stock->move(
                            $product,
                            StockMovementType::VoidRestore,
                            $restore,
                            $user,
                            'Void '.$bill->number,
                            $bill->id,
                            lock: false,
                        );
                    }
                }
            }

            $bill->installments()->update(['status' => InstallmentStatus::Cancelled->value]);
            $bill->payments()->delete();

            $bill->status = BillStatus::Voided;
            $bill->voided_at = now();
            $bill->voided_by = $user->id;
            $bill->void_reason = $reason ?: 'Same-day void';
            $bill->paid_fils = 0;
            $bill->save();

            return $bill;
        });
    }
}
