<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateLineData extends Model
{
    public $timestamps = false;

    public function lines()
    {
        return $this->morphMany(EstimateLine::class, 'lineable');
    }

    public function estimate_unit()
    {
        return $this->belongsTo(EstimateUnit::class);
    }
}
