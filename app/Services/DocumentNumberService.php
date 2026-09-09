<?php

namespace App\Services;

use App\Models\DocumentSequence;
use App\Models\ShopSetting;

class DocumentNumberService
{
    public function nextInvoiceNumber(): string
    {
        $settings = ShopSetting::current();

        return $this->next($settings->invoice_prefix, 'invoice');
    }

    public function nextCreditNoteNumber(): string
    {
        $settings = ShopSetting::current();

        return $this->next($settings->credit_note_prefix, 'credit_note');
    }

    private function next(string $prefix, string $type): string
    {
        $year = (int) now(config('app.timezone'))->format('Y');

        $sequence = DocumentSequence::query()
            ->where('type', $type)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            $sequence = DocumentSequence::query()->create([
                'type' => $type,
                'year' => $year,
                'last_number' => 0,
            ]);
            $sequence = DocumentSequence::query()
                ->whereKey($sequence->id)
                ->lockForUpdate()
                ->firstOrFail();
        }

        $sequence->last_number++;
        $sequence->save();

        return sprintf('%s-%d-%05d', $prefix, $year, $sequence->last_number);
    }
}
