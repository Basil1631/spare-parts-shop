<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->unsignedBigInteger('monthly_salary_fils')->default(0)->after('password');
            $table->decimal('incentive_percent', 5, 2)->default(0)->after('monthly_salary_fils');
            $table->boolean('is_active')->default(true)->after('incentive_percent');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('worked_on');
            $table->timestamp('first_login_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'worked_on']);
        });

        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->unsignedBigInteger('amount_fils');
            $table->foreignId('set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'period_start']);
        });

        Schema::create('branch_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('last_cost_fils')->default(0);
            $table->decimal('profit_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('floor_fils')->default(0);
            $table->timestamps();
            $table->unique(['branch_id', 'product_id']);
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('purchased_on');
            $table->unsignedBigInteger('total_fils')->default(0);
            $table->string('invoice_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('unit_cost_fils');
            $table->unsignedBigInteger('line_total_fils');
            $table->timestamps();
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('garage_id')->constrained()->nullOnDelete();
            $table->decimal('markup_percent', 5, 2)->default(0)->after('notes');
            $table->string('customer_kind')->nullable()->after('markup_percent');
        });

        Schema::table('bill_items', function (Blueprint $table) {
            $table->unsignedBigInteger('floor_unit_fils')->default(0)->after('unit_price_fils');
            $table->unsignedBigInteger('cost_fils')->default(0)->after('floor_unit_fils');
        });
    }

    public function down(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->dropColumn(['floor_unit_fils', 'cost_fils']);
        });
        Schema::table('bills', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['markup_percent', 'customer_kind']);
        });
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('branch_product_prices');
        Schema::dropIfExists('sales_targets');
        Schema::dropIfExists('attendance_logs');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['monthly_salary_fils', 'incentive_percent', 'is_active', 'last_login_at']);
        });
        Schema::dropIfExists('branches');
    }
};
