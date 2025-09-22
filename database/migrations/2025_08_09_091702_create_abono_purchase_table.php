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
            Schema::create('abono_purchase', function (Blueprint $table) {
                $table->id();
                $table->foreignId('abono_id')->constrained('abonos')->onDelete('cascade');
                $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
                $table->decimal('saldo_anterior', 15, 4);
                $table->decimal('monto_pagado', 15, 4);
                $table->decimal('saldo_actual', 15, 4);
                $table->softDeletes();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abono_purchase');
    }
};
