<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbonoPurchase extends Pivot
{
    use softDeletes;
    protected $table = 'abono_purchase';
    protected $fillable = [
        'abono_id',
        'purchase_id',
        'saldo_anterior',
        'monto_pagado',
        'saldo_actual'
    ];

}
