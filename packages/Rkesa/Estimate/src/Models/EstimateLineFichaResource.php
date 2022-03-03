<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Rkesa\Estimate\Models\Resource;

class EstimateLineFichaResource extends Model
{
    public function ficha()
    {
        return $this->belongsTo(EstimateLineFicha::class);
    }

    public function user()
    {
        //return $this->belongsTo(User::class);
    }

    public function estimate_unit()
    {
        return $this->belongsTo(EstimateUnit::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

}
