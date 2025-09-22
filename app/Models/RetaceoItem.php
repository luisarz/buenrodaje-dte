<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetaceoItem extends Model
{
    use  SoftDeletes;

    protected $table = 'retaceo_items';
    protected $fillable = [
        'retaceo_id',
        'purchase_id',
        'purchase_item_id',
        'inventory_id',
        'cantidad',
        'conf',
        'rec',
        'costo_unitario_factura',
        'fob',
        'flete',
        'seguro',
        'otro',
        'cif',
        'dai',
        'cif_dai',
        'gasto',
        'cif_dai_gasto',
        'precio',
        'iva',
        'costo',
        'precio_t',
        'estado',
    ];
    public function retaceo()
    {
        return $this->belongsTo(Sale::class);
    }
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }


}
