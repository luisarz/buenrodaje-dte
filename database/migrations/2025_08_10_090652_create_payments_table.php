<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date');
            $table->string('customer_name');
            $table->enum('customer_document_type', ['DUI', 'NIT', 'PASAPORTE'])->default('DUI');
            $table->string('customer_document_number')->nullable();
            $table->decimal('amount', 15, 4);
            $table->enum('method', ['Efectivo', 'Cheque', 'Transferencia','Criptos'])->default('Efectivo');
            $table->string('number_check')->nullable();
            $table->string('reference')->nullable();
            $table->string('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
