<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class ManufacturerContact extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'manufacturer_id'];
}
