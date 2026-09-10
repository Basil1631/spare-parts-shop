<?php

namespace Tests\Feature;

use App\Enums\PaymentType;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\BranchProductPrice;
use App\Models\Product;
use App\Models\User;
use App\Services\BillingService;
use App\Services\PayrollService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchOpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_marks_attendance(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/login', [
            'email' => 'staff@shop.local',
            'password' => 'password',
        ])->assertRedirect('/');

        $staff = User::query()->where('email', 'staff@shop.local')->firstOrFail();
        $this->assertTrue(AttendanceLog::query()->where('user_id', $staff->id)->whereDate('worked_on', now())->exists());
    }

    public function test_cannot_sell_below_floor_price(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@shop.local')->firstOrFail();
        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $admin->forceFill(['branch_id' => $branch->id])->save();

        $product = Product::query()->create([
            'name' => 'Rotor',
            'sku' => 'ROT-1',
            'price_fils' => 5000,
            'min_qty' => 0,
            'qty_on_hand' => 10,
            'active' => true,
        ]);

        BranchProductPrice::query()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'last_cost_fils' => 8000,
            'profit_percent' => 25,
            'floor_fils' => 10000,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('floor price');
        app(BillingService::class)->create(
            ['payment_type' => PaymentType::Cash->value],
            [['product_id' => $product->id, 'qty' => 1, 'unit_price_fils' => 9000]],
            $admin,
        );
    }

    public function test_incentive_is_percent_of_extra_sales(): void
    {
        $this->seed(DatabaseSeeder::class);
        $staff = User::query()->where('email', 'staff@shop.local')->firstOrFail();
        $staff->forceFill(['incentive_percent' => 10, 'monthly_salary_fils' => 100000])->save();
        $staff->salesTargets()->create([
            'period_start' => now()->startOfMonth()->toDateString(),
            'amount_fils' => 100000,
        ]);

        $pack = app(PayrollService::class)->monthPack($staff);
        $this->assertSame(100000, $pack['salary']);
        $this->assertSame(100000, $pack['target']);
        $this->assertSame(0, $pack['incentive']);
    }
}
