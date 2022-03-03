<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateGroupPayStage extends Model
{
    protected $fillable = ['estimate_group_id', 'pay_stage_id', 'paid', 'invoice_number', 'invoice_file', 'invoice_file_name', 'fact_paid', 'text', 'percent', 'payment_date'];

    protected $casts = [
        'paid' => 'boolean',
    ];

    public function estimate_group()
    {
        return $this->belongsTo(EstimateGroup::class);
    }

    public function pay_stage()
    {
        return $this->belongsTo(EstimatePayStage::class);
    }
}
