<?php

namespace Rkesa\Calendar\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistEntity extends Model
{
    protected $fillable = ['name', 'order', 'color'];
}
