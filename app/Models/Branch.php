<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Branch extends Model
{
    use HasFactory;
//    use LogsActivity;

    protected $softDelete = true;

    protected $fillable = ['stablisment_type_id',
        'name',
        'company_id',
        'nit',
        'nrc',
        'establishment_type_code',
        'pos_terminal_code',
        'departamento_id',
        'distrito_id', 'address',
        'economic_activity_id', 'phone',
        'email', 'web', 'prices_by_products', 'print',
        'logo', 'is_active'];

//    public function getActivitylogOptions(): LogOptions
//    {
//        return LogOptions::defaults()
//            ->logOnly(['name', 'company_id', 'nit', 'nrc', 'departamento_id', 'distrito_id', 'address', 'economic_activity_id', 'phone', 'email', 'web', 'prices_by_products', 'logo', 'is_active']);
//    }

    protected $casts = [
        'logo' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id', 'id');
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class, 'distrito_id', 'id');
    }

    public function economicactivity(): BelongsTo
    {
        return $this->belongsTo(EconomicActivity::class, 'economic_activity_id', 'id');
    }


    public function stablishmenttype(): BelongsTo
    {
        return $this->belongsTo(StablishmentType::class, 'stablisment_type_id', 'id');
    }

}
