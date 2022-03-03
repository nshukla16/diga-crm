<?php

namespace Rkesa\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rkesa\Dashboard\Models\DashboardEntity;

class ServiceState extends Model
{
    use SoftDeletes;

    protected $fillable = ['color', 'icon', 'name', 'type', 'horizontal', 'can_click', 'with_reason', 'order', 'destination_state_id'];

    public $timestamps = false;

    protected $dates = ['deleted_at'];

    public function service_state_actions()
    {
        return $this->hasMany(ServiceStateAction::class)->orderBy('order');
    }

    public function destination_state()
    {
        return $this->belongsTo(ServiceState::class, 'destination_state_id');
    }

    public function dashboard_entities()
    {
        // We use hasMany instead of hasOne because every ServiceState can be inside different dashboards
        return $this->hasMany(DashboardEntity::class);
    }
}
