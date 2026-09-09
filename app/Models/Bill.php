<?php

namespace App\Models;

use App\Enums\BillStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'number',
        'garage_id',
        'payment_type',
        'status',
        'billed_at',
        'credit_due_date',
        'subtotal_fils',
        'vat_fils',
        'total_fils',
        'paid_fils',
        'credited_fils',
        'garage_name',
        'garage_phone',
        'garage_address',
        'garage_trn',
        'created_by',
        'voided_at',
        'voided_by',
        'void_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_type' => PaymentType::class,
            'status' => BillStatus::class,
            'billed_at' => 'datetime',
            'credit_due_date' => 'date',
            'voided_at' => 'datetime',
            'subtotal_fils' => 'integer',
            'vat_fils' => 'integer',
            'total_fils' => 'integer',
            'paid_fils' => 'integer',
            'credited_fils' => 'integer',
        ];
    }

    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('sequence');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function outstandingFils(): int
    {
        return max(0, $this->total_fils - $this->paid_fils - $this->credited_fils);
    }

    public function isIssued(): bool
    {
        return $this->status === BillStatus::Issued;
    }

    public function isSameShopDay(): bool
    {
        return $this->billed_at?->timezone(config('app.timezone'))->isToday() ?? false;
    }

    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('status', BillStatus::Issued->value);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($term): void {
            $inner->where('number', 'like', '%'.$term.'%')
                ->orWhere('garage_name', 'like', '%'.$term.'%');
        });
    }
}
