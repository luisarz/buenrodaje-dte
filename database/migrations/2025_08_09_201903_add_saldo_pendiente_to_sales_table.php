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
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('saldo_pendiente', 15, 4)->default(0)->after('sale_total');
            $table->enum('order_condition', ['Contado', 'Crédito'])->default('Contado')->after('saldo_pendiente');
            $table->integer('credit_days')->nullable()->after('order_condition');
            $table->boolean('is_paid')->default(false)->after('credit_days');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('saldo_pendiente');
            $table->dropColumn('order_condition');
            $table->dropColumn('credit_days');
            $table->dropColumn('is_paid');

        });
    }
};
