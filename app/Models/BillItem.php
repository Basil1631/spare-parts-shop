<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    protected $fillable = [
        'bill_id',
        'product_id',
        'name',
        'sku',
        'qty',
        'unit_price_fils',
        'vat_rate',
        'line_subtotal_fils',
        'line_vat_fils',
        'line_total_fils',
        'returned_qty',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price_fils' => 'integer',
            'vat_rate' => 'decimal:2',
            'line_subtotal_fils' => 'integer',
            'line_vat_fils' => 'integer',
            'line_total_fils' => 'integer',
            'returned_qty' => 'integer',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returnableQty(): int
    {
        return max(0, $this->qty - $this->returned_qty);
    }
}
