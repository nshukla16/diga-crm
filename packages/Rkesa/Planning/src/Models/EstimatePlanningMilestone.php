<?php

namespace Rkesa\Planning\Models;

use Illuminate\Database\Eloquent\Model;

class EstimatePlanningMilestone extends Model
{
    protected $fillable = ['name', 'day', 'estimate_planning_id'];
    public function estimate_planning()
    {
        return $this->belongsTo(EstimatePlanning::class);
    }
}
