<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class EstimateMaterial extends Model
{
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
