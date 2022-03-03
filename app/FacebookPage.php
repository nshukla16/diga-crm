<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacebookPage extends Model
{
    public $timestamps = false;
    protected $fillable = ['page_id', 'name', 'url', 'logo', 'enabled'];
    protected $casts = [
        'enabled' => 'boolean',
    ];
}
