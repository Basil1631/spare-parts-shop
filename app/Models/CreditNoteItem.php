<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteItem extends Model
{
    protected $fillable = [
        'credit_note_id',
        'bill_item_id',
        'product_id',
        'qty',
        'line_subtotal_fils',
        'line_vat_fils',
        'line_total_fils',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'line_subtotal_fils' => 'integer',
            'line_vat_fils' => 'integer',
            'line_total_fils' => 'integer',
        ];
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function billItem(): BelongsTo
    {
        return $this->belongsTo(BillItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
