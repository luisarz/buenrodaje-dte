<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payments extends Model
{
    use softDeletes;
    //
    protected $table = 'payments';

    protected $fillable = [
        'payment_date',
        'customer_name',
        'customer_document_type',
        'customer_document_number',
        'amount',
        'method',
        'number_check',
        'reference',
        'description'
    ];
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:4',
    ];

    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'payment_sale', 'payment_id', 'sale_id')
            ->using(payments_sales::class)
            ->withPivot('amount_before', 'amount_payment', 'actual_amount')
            ->withTimestamps();
    }
    protected static function booted()
    {
        static::saved(function ($abono) {
            $abono->load('sales');
            $abono->actualizarSaldos();

        });

        static::deleted(function ($abono) {
            $abono->load('sales');
            $abono->actualizarSaldos();
        });
    }


    public function actualizarSaldos(): void
    {
        $this->load('sales');
        foreach ($this->sales as $sale) {
            $totalAbonos = $sale->payments()->sum('payment_sale.amount_payment');
            $sale->saldo_pendiente = max(0, $sale->sale_total - $totalAbonos);
            $sale->is_paid = $sale->saldo_pendiente <= 0;
            $sale->save();
        }
    }

}
