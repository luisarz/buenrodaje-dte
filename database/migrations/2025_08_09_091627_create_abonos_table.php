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
            Schema::create('abonos', function (Blueprint $table) {
                $table->id();
                $table->date('fecha_abono');
                $table->string('entity');
                $table->enum('document_type_entity', ['DUI', 'NIT', 'PASAPORTE'])->default('DUI');
                $table->string('document_number')->nullable();
                $table->decimal('monto', 15, 4);
                $table->enum('method', ['Efectivo', 'Cheque', 'Transferencia','Criptos'])->default('Efectivo');
                $table->string('numero_cheque')->nullable();
                $table->string('referencia')->nullable();
                $table->string('descripcion')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonos');
    }
};
