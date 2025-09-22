<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetaceoModel extends Model
{
    use softDeletes;
    protected $table = 'retaceo';
    protected $fillable = [
        'operation_date',
        'poliza_number',
        'retaceo_number',
        'fob',
        'flete',
        'seguro',
        'otros',
        'cif',
        'dai',
        'suma',
        'iva',
        'almacenaje',
        'custodia',
        'viaticos',
        'transporte',
        'descarga',
        'recarga',
        'otros_gastos',
        'total',
        'observaciones'
    ];
    //
    public function polizasPurchases()
    {
        return $this->belongsTo(Purchase::class, 'poliza_number', 'id');
    }
    public function items(){
        return $this->hasMany(RetaceoItem::class, 'retaceo_id', 'id');
    }
}
