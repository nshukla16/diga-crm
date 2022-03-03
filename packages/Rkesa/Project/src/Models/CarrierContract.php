<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class CarrierContract extends Model
{
    protected $fillable = ['name', 'file', 'file_name', 'carrier_id'];
}
