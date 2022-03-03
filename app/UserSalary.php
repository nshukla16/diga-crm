<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSalary extends Model
{
    protected $fillable = [
        'salary_type', 'amount', 'start', 'end', 'user_id'
    ];

    protected $casts = [
        'salary_type' => 'boolean'
    ];
}
