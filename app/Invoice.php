<?php

namespace App;

use App\InvoiceItem;
use App\InvoiceDocumentType;
use App\MovementType;
use App\VatExemptionReason;
use Rkesa\Client\Models\Client;
use Rkesa\Service\Models\Service;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Client\Models\ClientContact;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\EstimatePayStage;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_date',
        'invoice_no',
        'gross_total',
        'gross_total_without_vat',
        'client_id',
        'client_contact_id',
        'service_id',
        'estimate_id',
        'pay_stage_id',
        'payment_condition_id',
        'movement_type_id',
        'name',
        'address',
        'city',
        'code',
        'nif',
        'request',
        'currency',
        'exchange',
        'desc_cli',
        'desc_fin',
        'maturity',
        'postage',
        'other_services',
        'advances',
        'settlement',
        'vat_exemption_reason_id',
        'is_canceled',
        'canceling_reason',
        'is_exported',
        'is_final_consumer',
        'document_type_id',
        'parent_invoice_id',

        'loading_address',
        'loading_city',
        'loading_postal_code',
        'loading_country',
        'loading_date',

        'discharge_address',
        'discharge_city',
        'discharge_postal_code',
        'discharge_country',
        'discharge_registration',
        'discharge_date',

        'is_valued',
        'creator_id',
        'correction_reason',
        'global_discount',
        'series_id'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_canceled' => 'boolean',
        'is_exported' => 'boolean',
        'is_final_consumer' => 'boolean',
        'is_valued' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function client_contact()
    {
        return $this->belongsTo(ClientContact::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function pay_stage()
    {
        return $this->belongsTo(EstimatePayStage::class, 'pay_stage_id');
    }

    public function invoice_items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payment_condition()
    {
        return $this->belongsTo(PaymentCondition::class);
    }

    public function movement_type()
    {
        return $this->belongsTo(MovementType::class);
    }

    public function vat_exemption_reason()
    {
        return $this->belongsTo(VatExemptionReason::class);
    }

    public function invoice_document_type()
    {
        return $this->belongsTo(InvoiceDocumentType::class, 'document_type_id');
    }

    public function parent()
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    public function children()
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }
}
