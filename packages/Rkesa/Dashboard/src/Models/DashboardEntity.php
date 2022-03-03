<?php

namespace Rkesa\Dashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Dashboard\Models\Dashboard;
use Rkesa\Dashboard\Models\DashboardEntityField;
use Rkesa\Dashboard\Models\DashboardWidget;
use Rkesa\Service\Models\ServiceState;

class DashboardEntity extends Model
{
    protected $table = 'dashboard_entities';
    public $timestamps = false;
    protected $fillable = ['hide'];

    public function state()
    {
        return $this->hasOne(ServiceState::class, 'id', 'service_state_id');
    }

    public function fields()
    {
        return $this->hasMany(DashboardEntityField::class);
    }

    

}
