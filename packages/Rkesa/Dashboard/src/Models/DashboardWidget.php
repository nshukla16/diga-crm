<?php

namespace Rkesa\Dashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Dashboard\Models\DashboardEntity;
use Rkesa\Service\Models\ServiceState;

class DashboardWidget extends Model
{
    protected $table = 'dashboard_widgets';
    public $timestamps = false;
    protected $fillable = ['type', 'data_type', 'color1', 'color2', 'color3', 'color4', 'data'];
    
    public function state()
    {
        return $this->hasOne(ServiceState::class, 'id', 'service_state_id');
    }

}
