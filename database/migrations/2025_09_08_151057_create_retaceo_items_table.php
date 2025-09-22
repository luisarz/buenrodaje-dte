<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('retaceo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retaceo_id')->constrained('retaceo');
            $table->foreignId('purchase_id')->constrained('purchases');
            $table->foreignId('purchase_item_id')->constrained('purchase_items');
            $table->foreignId('inventory_id')->constrained('inventories');
            $table->integer('cantidad')->default(0);
            $table->integer('conf')->default(0);
            $table->integer('rec')->default(1);
            $table->decimal('costo_unitario_factura', 20, 8)->default(0.00);
            $table->decimal('fob', 20, 8)->default(0.00);
            $table->decimal('flete', 20, 8)->default(0.00);
            $table->decimal('seguro', 20, 8)->default(0.00);
            $table->decimal('otro', 20, 8)->default(0.00);
            $table->decimal('cif', 20, 8)->default(0.00);
            $table->decimal('dai', 20, 8)->default(0.00);
            $table->decimal('cif_dai', 20, 8)->default(0.00);
            $table->decimal('gasto', 20, 8)->default(0.00);
            $table->decimal('cif_dai_gasto', 20, 8)->default(0.00);
            $table->decimal('precio', 20, 8)->default(0.00);
            $table->decimal('iva', 20, 8)->default(0.00);
            $table->decimal('costo', 20, 8)->default(0.00);
            $table->decimal('precio_t', 20, 8)->default(0.00);
            $table->enum('estado', [0, 1,3])->default(0)->comment('0 - pendiente, 1 - procesado, 3 - cancelado');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retaceo_items');
    }
};
