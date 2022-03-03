<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'create', 'read', 'update', 'delete', 'addit'];
}
