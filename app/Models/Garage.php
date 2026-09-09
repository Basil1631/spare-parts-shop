<?php

namespace App\Models;

use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Garage extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'trn',
        'payment_type',
        'credit_days',
        'installment_count',
    ];

    protected function casts(): array
    {
        return [
            'payment_type' => PaymentType::class,
            'credit_days' => 'integer',
            'installment_count' => 'integer',
        ];
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function outstandingFils(): int
    {
        return (int) $this->bills()->issued()->get()->sum(fn (Bill $bill) => $bill->outstandingFils());
    }
}
