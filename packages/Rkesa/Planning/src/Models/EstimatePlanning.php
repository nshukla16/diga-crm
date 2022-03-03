<?php

namespace Rkesa\Planning\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\Estimate;

class EstimatePlanning extends Model
{
    protected $fillable = ['estimate_id', 'name', 'is_custom'];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function estimate_planning_lines()
    {
        return $this->hasMany(EstimatePlanningLine::class)->orderBy('parent_id')->orderBy('order_number');
    }

    public function estimate_milestones()
    {
        return $this->hasMany(EstimatePlanningMilestone::class);
    }
}
