<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'qty',
        'received_qty',
        'unit_cost_fils',
        'line_total_fils',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'received_qty' => 'integer',
            'unit_cost_fils' => 'integer',
            'line_total_fils' => 'integer',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
