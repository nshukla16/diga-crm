<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateChange extends Model
{
    public function user()
    {
        //return $this->belongsTo(User::class);
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
