<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
        'monthly_salary_fils',
        'incentive_percent',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'monthly_salary_fils' => 'integer',
            'incentive_percent' => 'decimal:2',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function salesTargets(): HasMany
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isBranchManager(): bool
    {
        return $this->hasRole('branch_manager');
    }

    public function canSeeAllBranches(): bool
    {
        return $this->isAdmin();
    }

    public function canManageStaff(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager']);
    }

    public function canSeeHr(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager', 'accountant']);
    }

    public function canManageCatalog(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager']);
    }

    public function canBill(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager', 'sales', 'staff']);
    }

    public function canPurchase(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager', 'purchase']);
    }

    public function canConfirmGodown(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager', 'godown_supervisor']);
    }

    public function canManageGodownStock(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager', 'godown_supervisor']);
    }

    public function canSeeVendorBills(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager', 'accountant']);
    }

    public function canSeeStock(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager', 'purchase', 'godown_supervisor']);
    }

    public function canCollect(): bool
    {
        return $this->hasAnyRole(['admin', 'branch_manager', 'accountant', 'sales', 'staff']);
    }

    public function scopeInBranch(Builder $query, ?int $branchId): Builder
    {
        if (! $branchId) {
            return $query;
        }

        return $query->where('branch_id', $branchId);
    }
}
