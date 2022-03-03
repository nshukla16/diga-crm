<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class EstimateWorker extends Model
{
    protected $fillable = ['group_id', 'estimate_id', 'user_id', 'price'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

}
