<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'branch_id',
        'created_by',
        'supplier_name',
        'invoice_number',
        'purchased_on',
        'total_fils',
        'invoice_path',
        'notes',
        'status',
        'received_by',
        'received_at',
        'paid_by',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'purchased_on' => 'date',
            'total_fils' => 'integer',
            'status' => PurchaseStatus::class,
            'received_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isAwaitingGodown(): bool
    {
        return $this->status === PurchaseStatus::AwaitingGodown;
    }

    public function isReceived(): bool
    {
        return $this->status === PurchaseStatus::Received;
    }

    public function isPaid(): bool
    {
        return $this->status === PurchaseStatus::Paid;
    }

    public function scopeAwaitingGodown(Builder $query): Builder
    {
        return $query->where('status', PurchaseStatus::AwaitingGodown->value);
    }

    public function scopeForAccountant(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PurchaseStatus::Received->value,
            PurchaseStatus::Paid->value,
        ]);
    }
}
