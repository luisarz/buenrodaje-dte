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
        Schema::create('retaceo', function (Blueprint $table) {
            $table->id();
            $table->date('operation_date');
            $table->string('poliza_number');
            $table->string('retaceo_number');
            $table->decimal('fob', 20, 8)->default(0.00);
            $table->decimal('flete', 20, 8)->default(0.00);
            $table->decimal('seguro', 20, 8)->default(0.00);
            $table->decimal('otros', 20, 8)->default(0.00);
            $table->decimal('cif', 20, 8)->default(0.00);
            $table->decimal('dai', 20, 8)->default(0.00);
            $table->decimal('suma', 20, 8)->default(0.00);
            $table->decimal('iva', 20, 8)->default(0.00);
            $table->decimal('almacenaje', 20, 8)->default(0.00);
            $table->decimal('custodia', 20, 8)->default(0.00);
            $table->decimal('viaticos', 20, 8)->default(0.00);
            $table->decimal('transporte', 20, 8)->default(0.00);
            $table->decimal('descarga', 20, 8)->default(0.00);
            $table->decimal('recarga', 20, 8)->default(0.00);
            $table->decimal('otros_gastos', 20, 8)->default(0.00);
            $table->decimal('total', 20, 8)->default(0.00);
            $table->string('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retaceo_models');
    }
};
