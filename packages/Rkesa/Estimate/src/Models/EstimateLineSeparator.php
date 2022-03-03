<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateLineSeparator extends Model
{
    public $timestamps = false;

    public function lines()
    {
        return $this->morphMany(EstimateLine::class, 'lineable');
    }
}
