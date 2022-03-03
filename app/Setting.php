<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    const ALLOWED = [
        'planning_working_hours_start',
        'planning_working_hours_end',
        'accountant_user_id',
        'default_color_of_roadmap_task',
        'make_auto_calculation_for_payment_steps',
        'construction_manager',
        'construction_manager_list'
    ];

    public static function associative_array()
    {
        $arr = [];
        foreach(Setting::all() as $setting){
            $arr[$setting->key] = $setting->value;
        }
        return $arr;
    }
}
