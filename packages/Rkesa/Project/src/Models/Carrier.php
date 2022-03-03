<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    protected $fillable = [
        'name'
    ];

    public function contracts()
    {
        return $this->hasMany(CarrierContract::class);
    }
}
