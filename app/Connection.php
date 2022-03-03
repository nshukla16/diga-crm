<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    protected $casts = [
        'is_approved' => 'boolean',
    ];
}
