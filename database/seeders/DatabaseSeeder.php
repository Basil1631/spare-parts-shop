<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ShopSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'branch_manager', 'sales', 'purchase', 'accountant', 'godown_supervisor', 'staff'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        $branch = Branch::query()->firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main branch', 'active' => true]
        );

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@shop.local'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $admin->syncRoles(['admin']);

        $manager = User::query()->firstOrCreate(
            ['email' => 'manager@shop.local'],
            [
                'name' => 'Branch Manager',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'monthly_salary_fils' => 800000,
                'incentive_percent' => 3,
                'is_active' => true,
            ]
        );
        $manager->forceFill(['branch_id' => $branch->id])->save();
        $manager->syncRoles(['branch_manager']);

        $staff = User::query()->firstOrCreate(
            ['email' => 'staff@shop.local'],
            [
                'name' => 'Counter Staff',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'monthly_salary_fils' => 400000,
                'incentive_percent' => 5,
                'is_active' => true,
            ]
        );
        $staff->forceFill(['branch_id' => $branch->id])->save();
        $staff->syncRoles(['sales', 'staff']);

        $purchase = User::query()->firstOrCreate(
            ['email' => 'purchase@shop.local'],
            [
                'name' => 'Purchase Manager',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'monthly_salary_fils' => 450000,
                'incentive_percent' => 0,
                'is_active' => true,
            ]
        );
        $purchase->forceFill(['branch_id' => $branch->id])->save();
        $purchase->syncRoles(['purchase']);

        $accountant = User::query()->firstOrCreate(
            ['email' => 'accounts@shop.local'],
            [
                'name' => 'Accountant',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'monthly_salary_fils' => 500000,
                'incentive_percent' => 0,
                'is_active' => true,
            ]
        );
        $accountant->forceFill(['branch_id' => $branch->id])->save();
        $accountant->syncRoles(['accountant']);

        $godown = User::query()->firstOrCreate(
            ['email' => 'godown@shop.local'],
            [
                'name' => 'Godown Supervisor',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'monthly_salary_fils' => 420000,
                'incentive_percent' => 0,
                'is_active' => true,
            ]
        );
        $godown->forceFill(['branch_id' => $branch->id])->save();
        $godown->syncRoles(['godown_supervisor']);

        ShopSetting::current();
    }
}
