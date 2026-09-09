<?php

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\InstallmentStatus;
use App\Enums\PaymentType;
use App\Models\Bill;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CollectionService
{
    public function record(Bill $bill, int $amountFils, User $user, ?int $installmentId = null, string $method = 'cash', ?string $note = null): Payment
    {
        if ($amountFils <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($bill, $amountFils, $user, $installmentId, $method, $note) {
            $bill = Bill::query()->whereKey($bill->id)->lockForUpdate()->firstOrFail();

            if ($bill->status !== BillStatus::Issued) {
                throw new RuntimeException('Cannot collect on a voided bill.');
            }

            if ($bill->payment_type === PaymentType::Cash) {
                throw new RuntimeException('Ready cash bills are already paid.');
            }

            $remaining = $bill->outstandingFils();
            if ($amountFils > $remaining) {
                throw new RuntimeException('Amount exceeds outstanding AED '.Money::fromFils($remaining).'.');
            }

            $installment = null;
            if ($installmentId) {
                $installment = Installment::query()
                    ->where('bill_id', $bill->id)
                    ->whereKey($installmentId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $installment->isOpen()) {
                    throw new RuntimeException('That installment is not open.');
                }

                if ($amountFils > $installment->outstandingFils()) {
                    throw new RuntimeException('Amount exceeds this installment.');
                }

                $installment->paid_fils += $amountFils;
                $installment->refreshStatus();
            } elseif ($bill->payment_type === PaymentType::Installment) {
                $left = $amountFils;
                $installments = $bill->installments()->open()->orderBy('due_date')->lockForUpdate()->get();
                foreach ($installments as $row) {
                    if ($left <= 0) {
                        break;
                    }
                    $apply = min($left, $row->outstandingFils());
                    $row->paid_fils += $apply;
                    $row->refreshStatus();
                    $left -= $apply;
                }
            }

            $payment = Payment::query()->create([
                'garage_id' => $bill->garage_id,
                'bill_id' => $bill->id,
                'installment_id' => $installment?->id,
                'amount_fils' => $amountFils,
                'paid_at' => now(),
                'method' => $method,
                'user_id' => $user->id,
                'note' => $note,
            ]);

            $bill->paid_fils += $amountFils;
            $bill->save();

            return $payment;
        });
    }
}
