<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku');
            $table->string('sku_normalized')->unique();
            $table->unsignedBigInteger('price_fils');
            $table->decimal('vat_rate', 5, 2)->nullable();
            $table->unsignedInteger('min_qty')->default(0);
            $table->integer('qty_on_hand')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('garages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('trn')->nullable();
            $table->string('payment_type')->default('cash');
            $table->unsignedInteger('credit_days')->nullable();
            $table->unsignedInteger('installment_count')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garages');
        Schema::dropIfExists('products');
    }
};
