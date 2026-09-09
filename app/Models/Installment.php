<?php

namespace App\Models;

use App\Enums\InstallmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Installment extends Model
{
    protected $fillable = [
        'bill_id',
        'sequence',
        'amount_fils',
        'due_date',
        'paid_fils',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'amount_fils' => 'integer',
            'due_date' => 'date',
            'paid_fils' => 'integer',
            'status' => InstallmentStatus::class,
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function outstandingFils(): int
    {
        return max(0, $this->amount_fils - $this->paid_fils);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [InstallmentStatus::Pending, InstallmentStatus::Partial], true);
    }

    public function refreshStatus(): void
    {
        if ($this->status === InstallmentStatus::Cancelled) {
            return;
        }

        if ($this->paid_fils <= 0) {
            $this->status = InstallmentStatus::Pending;
        } elseif ($this->paid_fils >= $this->amount_fils) {
            $this->status = InstallmentStatus::Paid;
        } else {
            $this->status = InstallmentStatus::Partial;
        }

        $this->save();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [InstallmentStatus::Pending->value, InstallmentStatus::Partial->value]);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->open()->whereDate('due_date', '<=', now()->toDateString());
    }
}
