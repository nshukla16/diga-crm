<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\User;

class EstimateLineWorker extends Model
{
    public function estimate_line()
    {
        return $this->belongsTo(EstimateLine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
