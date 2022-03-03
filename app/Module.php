<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    public $timestamps = false;

    public static function allowed()
    {
        $modules = Module::all();

        $restricted = ['client', 'service', 'crm'];

        return $modules->filter(function ($value, $key) use($restricted) {
            return array_search($value->name, $restricted) === false; 
        })->values()->all();
    }

    protected $casts = [
        'trial_date_start' => 'datetime:Y-m-d',
        'trial_date_end' => 'datetime:Y-m-d',
        'current_subscription_date_start' => 'datetime:Y-m-d',
        'current_subscription_date_end' => 'datetime:Y-m-d'
    ];

    public function tariffs()
    {
        return $this->belongsToMany('App\Models\Tariff');
    }
}
