<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Provider;


class Purchase extends Model
{
    use softDeletes;

    protected $fillable = [
        'provider_id',
        'employee_id',
        'wherehouse_id',
        'retaced_status',
        'retaceo_number',
        'purchase_date',
        'process_document_type',
        'document_type',
        'document_number',
        'pruchase_condition',
        'credit_days',
        'status',
        'have_perception',
        'net_value',
        'taxe_value',
        'perception_value',
        'purchase_total',
        'paid',
        'saldo_pendiente',
        'purchase_type',
    ];

    protected function casts(): array
    {
        return [
            'purchase_type'=>'integer',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }


    public function wherehouse(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);

    }

    public function abonos(): BelongsToMany
    {
        return $this->belongsToMany(Abono::class, 'abono_purchase')
            ->using(AbonoPurchase::class)
            ->withPivot('monto_pagado')
            ->withTimestamps();
    }

//    public function retaceo()
//    {
//        return $this->belongsTo(RetaceoModel::class, 'id', 'poliza_number');
//    }
}
