<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $casts = [
        'trial_date_start' => 'datetime:Y-m-d',
        'trial_date_end' => 'datetime:Y-m-d',
        'current_subscription_date_start' => 'datetime:Y-m-d',
        'current_subscription_date_end' => 'datetime:Y-m-d'
    ];

    public function modules()
    {
        return $this->belongsToMany('App\Module', 'tariff_module');
    }
}
