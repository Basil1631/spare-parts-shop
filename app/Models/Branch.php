<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'phone',
        'address',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function manager(): HasOne
    {
        return $this->hasOne(User::class)->whereHas('roles', fn ($q) => $q->where('name', 'branch_manager'));
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(BranchProductPrice::class);
    }
}
