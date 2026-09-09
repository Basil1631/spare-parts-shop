<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('garage_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_type');
            $table->string('status')->default('issued');
            $table->timestamp('billed_at');
            $table->date('credit_due_date')->nullable();
            $table->unsignedBigInteger('subtotal_fils')->default(0);
            $table->unsignedBigInteger('vat_fils')->default(0);
            $table->unsignedBigInteger('total_fils')->default(0);
            $table->unsignedBigInteger('paid_fils')->default(0);
            $table->unsignedBigInteger('credited_fils')->default(0);
            $table->string('garage_name')->nullable();
            $table->string('garage_phone')->nullable();
            $table->text('garage_address')->nullable();
            $table->string('garage_trn')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('void_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku');
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('unit_price_fils');
            $table->decimal('vat_rate', 5, 2);
            $table->unsignedBigInteger('line_subtotal_fils');
            $table->unsignedBigInteger('line_vat_fils');
            $table->unsignedBigInteger('line_total_fils');
            $table->unsignedInteger('returned_qty')->default(0);
            $table->timestamps();
        });

        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->unsignedBigInteger('amount_fils');
            $table->date('due_date');
            $table->unsignedBigInteger('paid_fils')->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garage_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('installment_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount_fils');
            $table->timestamp('paid_at');
            $table->string('method')->default('cash');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            $table->unsignedBigInteger('subtotal_fils')->default(0);
            $table->unsignedBigInteger('vat_fils')->default(0);
            $table->unsignedBigInteger('total_fils')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bill_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('line_subtotal_fils');
            $table->unsignedBigInteger('line_vat_fils');
            $table->unsignedBigInteger('line_total_fils');
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedInteger('qty');
            $table->foreignId('bill_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('credit_note_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('credit_note_items');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('installments');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
    }
};
