<?php

namespace App\Enums;

enum StaffRole: string
{
    case Admin = 'admin';
    case BranchManager = 'branch_manager';
    case Sales = 'sales';
    case Purchase = 'purchase';
    case Accountant = 'accountant';
    case GodownSupervisor = 'godown_supervisor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::BranchManager => 'Branch manager',
            self::Sales => 'Sales staff',
            self::Purchase => 'Purchase manager',
            self::Accountant => 'Accountant',
            self::GodownSupervisor => 'Godown supervisor',
        };
    }

    /** @return list<self> */
    public static function branchStaff(): array
    {
        return [self::Sales, self::Purchase, self::Accountant, self::GodownSupervisor];
    }
}
