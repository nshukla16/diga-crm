<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContractorServicePayStage extends Model
{
    protected $table = 'contractor_service_pay_stages';

    protected $casts = [
        'paid' => 'boolean'
    ];
}
