<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckfrontField extends Model
{
    public $timestamps = false;
    protected $fillable = ['field_name', 'destination', 'order', 'type', 'note'];
}
