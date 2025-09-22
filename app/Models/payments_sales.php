<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class payments_sales extends Pivot
{

    protected $table = 'payment_sale';

    protected $fillable = [
        'payment_id',
        'sale_id',
        'amount_before',
        'amount_payment',
        'actual_amount',
    ];

    protected $casts = [
        'amount_before' => 'decimal:4',
        'amount_payment' => 'decimal:4',
        'actual_amount' => 'decimal:4',
    ];
}
