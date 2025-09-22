<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    protected $table = 'sales';
    use softDeletes;

    protected $fillable = [
        'cashbox_open_id',
        'operation_date',
        'document_type_id',
        'document_internal_number',
        'wherehouse_id',
        'seller_id',
        'mechanic_id',
        'customer_id',
        'operation_condition_id',
        'payment_method_id',
        'sales_payment_status',
        'sale_status',
        'is_taxed',
        'have_retention',
        'net_amount',
        'taxe',
        'is_rate',
        'rate_amount',
        'discount',
        'retention',
        'sale_total',
        'cash',
        'change',
        'casher_id',
        'billing_model',
        'transmision_type',
        'is_dte',
        'is_hacienda_send',
        'generationCode',
        'receiptStamp',
        'jsonUrl',
        'operation_type',
        'is_order_closed_without_invoiced',
        'is_invoiced',
        'order_number',
        'discount_percentage',
        'discount_money',
        'total_order_after_discount',
        'document_related_id',
        'condition_id',
        'credit_days'
    ];


    public function wherehouse(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    public function saleRelated(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'document_related_id', 'id');
    }

    public function documenttype(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Employee::class);

    }
    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Employee::class);

    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class,'customer_id','id');
    }
    public  function salescondition(): BelongsTo
    {
        return $this->belongsTo(OperationCondition::class, 'operation_condition_id');

    }
    public function paymentmethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id','id');
    }
    public function casher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'casher_id');
    }
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'sale_id','id');
    }
    public function inventories(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function dteProcesado(): HasOne
    {
        return $this->hasOne(HistoryDte::class,'sales_invoice_id');
    }

    public function billingModel(): BelongsTo
    {
        return $this->belongsTo(BillingModel::class,'billing_model','id');

    }
    public function transmisionType(): BelongsTo
    {
        return $this->belongsTo(TransmisionType::class,'transmision_type','id');

    }
}