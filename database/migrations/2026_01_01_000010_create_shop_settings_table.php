<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name')->default('Spare Parts Shop');
            $table->text('address')->nullable();
            $table->string('trn')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('vat_percent', 5, 2)->default(5.00);
            $table->string('invoice_prefix')->default('INV');
            $table->string('credit_note_prefix')->default('CN');
            $table->unsignedTinyInteger('installment_day')->default(3);
            $table->timestamps();
        });

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->unique(['type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('shop_settings');
    }
};
