<?php

namespace Rkesa\FinancialCalendar\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimatePayStage;
use Rkesa\Service\Models\Service;

class PaymentEvent extends Model
{
    protected $fillable = [
        'title',
        'amount',
        'start',
        'description',
        'estimate_id',
        'client_id',
        'contact_id',
        'service_id',
        'status_id',
        'comment'
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function contact()
    {
        return $this->belongsTo(ClientContact::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function estimate_pay_stage()
    {
        return $this->belongsTo(EstimatePayStage::class);
    } 
}
