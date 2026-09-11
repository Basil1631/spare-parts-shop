<?php

use App\Enums\PurchaseStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('status')->default(PurchaseStatus::AwaitingGodown->value)->after('notes');
            $table->foreignId('received_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable()->after('received_by');
            $table->foreignId('paid_by')->nullable()->after('received_at')->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable()->after('paid_by');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->unsignedInteger('received_qty')->default(0)->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('received_qty');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('received_by');
            $table->dropConstrainedForeignId('paid_by');
            $table->dropColumn(['status', 'received_at', 'paid_at']);
        });
    }
};
