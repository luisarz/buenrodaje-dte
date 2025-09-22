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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            //Informacion general
            $table->foreignId('wherehouse_id')->constrained('branches')->cascadeOnDelete();//Sucursal
            $table->foreignId('cashbox_open_id')->nullable()->constrained('cash_box_opens')->cascadeOnDelete();//Apertura de caja
            $table->date('operation_date')->default(now());
             $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();//Cliente
            $table->foreignId('operation_condition_id')->nullable()->constrained('operation_conditions')->cascadeOnDelete();//Condicion de operacion contado, credito
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->cascadeOnDelete();//Metodo de pago cheque, efectivo, tarjeta
            $table->enum('sales_payment_status',['Pagada','Pendiente','Abono'])->nullable();
            $table->enum('sale_status',['Nueva','Procesando','Cancelada','Facturada','Anulado','Finalizado'])->default('Nueva');
            $table->foreignId('seller_id')->constrained('employees')->cascadeOnDelete();//Vendedor
//            $table->string('status')->nullable();

            //Datos tributarios

            $table->boolean('is_taxed')->default(true);
            $table->decimal('taxe',10,2)->default(0);
            $table->boolean('have_retention')->default(false);
            $table->decimal('retention',10,2)->default(0);
            $table->decimal('net_amount',10,2)->default(0);
            $table->decimal('discount',10,2)->default(0);
            $table->decimal('sale_total',10,2)->default(0);
//            $table->decimal('pending_sale',10,2)->default(0);

            //Datos Hacienda
            $table->foreignId('document_type_id')->nullable()->constrained('document_types')->cascadeOnDelete();//factura, nota de venta, etc
            $table->string('document_internal_number')->nullable(); //Control interno correlativos caja

            $table->foreignId('billing_model')->nullable()->constrained('billing_models')->cascadeOnDelete();
            $table->foreignId('transmision_type')->nullable()->constrained('transmision_types')->cascadeOnDelete();
            $table->boolean('is_dte')->default(false);
            $table->boolean('is_hacienda_send')->default(false);
            $table->string('generationCode')->nullable();
            $table->string('receiptStamp')->nullable();
            $table->string('jsonUrl')->nullable();
            $table->string('num_control')->nullable();

            //Datos vendedor y mecánico
            $table->foreignId('casher_id')->nullable()->constrained('employees')->cascadeOnDelete();//Cajero
            $table->foreignId('mechanic_id')->nullable()->constrained('employees')->cascadeOnDelete();//Cajero

            //Datos caja
            $table->decimal('cash',10,2)->default(0);
            $table->decimal('change',10,2)->default(0);
            $table->boolean('is_order_closed_without_invoiced')->default(false);
            $table->boolean('is_invoiced')->default(false);
            $table->enum('operation_type',['Sale','Quote','Order','Sales Remittance','Other','ND','NC','NR'])->default('Sale');

            //Datos de Ordenes
            $table->string('order_number')->nullable();
            $table->decimal('discount_percentage',10,2)->default(0);
            $table->decimal('discount_money',10,2)->default(0);
            $table->decimal('total_order_after_discount',10,2)->default(0);

            //Datos de crédito
            $table->enum('order_condition', ['Contado', 'Crédito'])->default('Contado');
            $table->decimal('saldo_pendiente', 15, 4)->default(0);
            $table->integer('order_credit_days')->nullable();
            $table->boolean('is_paid')->default(false);


            //Documentos relacionados
            $table->foreignId('document_related_id')->nullable()->constrained('sales'); //Numero de documento, factura, nota de venta, etc



            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};