<?php
// app/Models/Abono.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Abono extends Model
{
    use softDeletes;
    protected $table = 'abonos';

    protected $fillable = [
        'fecha_abono',
        'entity',
        'document_type_entity',
        'document_number',
        'monto',
        'method',
        'numero_cheque',
        'referencia',
        'descripcion',
    ];


    protected $dates = [
        'fecha_abono',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];


    protected $casts = [
        'fecha_abono' => 'date',
        'monto' => 'decimal:4',
    ];

    public function purchases(): BelongsToMany
    {
        return $this->belongsToMany(Purchase::class, 'abono_purchase')
            ->using(AbonoPurchase::class)
            ->withPivot('saldo_anterior', 'monto_pagado', 'saldo_actual')
            ->withTimestamps();
    }


    protected static function booted()
    {
        static::saved(function ($abono) {
            $abono->load('purchases');
            $abono->actualizarSaldos();

        });

        static::deleted(function ($abono) {
            // Cargar las compras relacionadas antes de eliminar
            $abono->load('purchases');
            $abono->actualizarSaldos();
        });
    }


    public function actualizarSaldos(): void
    {
        $this->load('purchases');
        foreach ($this->purchases as $purchase) {
            $totalAbonos = $purchase->abonos()->sum('abono_purchase.monto_pagado');
            $purchase->saldo_pendiente = max(0, $purchase->purchase_total - $totalAbonos);
            $purchase->paid = $purchase->saldo_pendiente <= 0;
            $purchase->save();
        }
    }


}