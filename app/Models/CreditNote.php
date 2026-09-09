<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditNote extends Model
{
    protected $fillable = [
        'number',
        'bill_id',
        'reason',
        'subtotal_fils',
        'vat_fils',
        'total_fils',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_fils' => 'integer',
            'vat_fils' => 'integer',
            'total_fils' => 'integer',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
