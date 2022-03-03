<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\EstimateGroupWorker;

class AuthPhoto extends Model
{
    public function estimate_group_worker()
    {
        return $this->belongsTo(EstimateGroupWorker::class);
    }
}
