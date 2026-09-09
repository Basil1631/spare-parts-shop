<?php

namespace Database\Seeders;

use App\Models\ShopSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::findOrCreate('admin', 'web');
        $staffRole = Role::findOrCreate('staff', 'web');

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@shop.local'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );
        $admin->syncRoles([$adminRole]);

        $staff = User::query()->firstOrCreate(
            ['email' => 'staff@shop.local'],
            ['name' => 'Counter Staff', 'password' => Hash::make('password')]
        );
        $staff->syncRoles([$staffRole]);

        ShopSetting::current();
    }
}
