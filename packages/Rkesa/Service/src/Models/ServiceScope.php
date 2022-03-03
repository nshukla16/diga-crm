<?php

namespace Rkesa\Service\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceScope extends Model
{
    protected $fillable = ['start_service_state_id', 'end_service_state_id', 'name'];

    public function start_service_state()
    {
        return $this->belongsTo(ServiceState::class, 'start_service_state_id');
    }

    public function end_service_state()
    {
        return $this->belongsTo(ServiceState::class, 'end_service_state_id');
    }
}
