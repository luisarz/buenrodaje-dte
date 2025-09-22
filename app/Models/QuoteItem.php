<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    protected $table = 'sale_items';
    protected $fillable = [
        'sale_id',
        'inventory_id',
        'description',
        'quantity',
        'price',
        'discount',
        'total',
        'is_except',
        'exemptSale',
        'tributes',
    ];
    protected $casts = [
        'tributes' => 'array',
    ];
    public function sale()
    {
        return $this->belongsTo(QuoteModel::class);
    }
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    public function whereHouse()
    {
        return $this->belongsTo(Branch::class, 'wherehouse_id','id');

    }
}
