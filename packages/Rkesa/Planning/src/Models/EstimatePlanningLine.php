<?php

namespace Rkesa\Planning\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\EstimateLine;

class EstimatePlanningLine extends Model
{
    protected $fillable = [
        'estimate_planning_id',
        'start_datetime',
        'estimate_planning_id',
        'end_datetime',
        'progress',
        'description',
        'predecessor',
        'line_number',
        'name',
        'parent_id',
        'order_number',
    ];

    public function estimate_planning()
    {
        return $this->belongsTo(EstimatePlanning::class);
    }

    public function estimate_line()
    {
        return $this->belongsTo(EstimateLine::class);
    }
}
