<?php

namespace Rkesa\Hr\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\Estimate;
use App\User;

class Timetracker extends Model
{
    protected $table = 'timetracker';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'estimate_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function estimate() {
        return $this->belongsTo(Estimate::class);
    }
}
