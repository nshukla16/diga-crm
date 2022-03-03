<?php

namespace Rkesa\CalendarExtended\Models;

use Illuminate\Database\Eloquent\Model;

class EventGroup extends Model
{
    protected $fillable = ['color', 'name'];

    public $timestamps = false;
}
