<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class ManufacturerContract extends Model
{
    protected $fillable = ['name', 'file', 'file_name', 'manufacturer_id'];
}
