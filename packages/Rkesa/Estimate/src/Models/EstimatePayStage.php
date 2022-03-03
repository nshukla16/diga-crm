<?php

namespace Rkesa\Estimate\Models;

use App\EmailTemplate;
use App\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstimatePayStage extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['percent', 'text', 'estimate_id', 'payment_date', 'vat_type', 'invoice_file', 'invoice_file_name', 'recibo_file', 'recibo_file_name', 'paid', 'invoice_number', 'fact_paid', 'email_template_id', 'proof_file', 'proof_file_name'];

    protected $casts = [
        'paid' => 'boolean',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function estimate_group_pay_stages()
    {
        return $this->hasMany(EstimateGroupPayStage::class, 'pay_stage_id', 'id');
    }

    public function email_template()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'pay_stage_id', 'id');
    }
}
