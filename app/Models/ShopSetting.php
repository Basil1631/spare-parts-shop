<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'shop_name',
        'address',
        'trn',
        'phone',
        'vat_percent',
        'invoice_prefix',
        'credit_note_prefix',
        'installment_day',
    ];

    protected function casts(): array
    {
        return [
            'vat_percent' => 'decimal:2',
            'installment_day' => 'integer',
        ];
    }

    public static function current(): self
    {
        $row = static::query()->first();

        if ($row) {
            return $row;
        }

        return static::query()->create([
            'shop_name' => 'Spare Parts Shop',
            'vat_percent' => 5,
            'invoice_prefix' => 'INV',
            'credit_note_prefix' => 'CN',
            'installment_day' => 3,
        ]);
    }
}
