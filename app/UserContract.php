<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserContract extends Model
{
    protected $fillable = ['user_id', 'begin', 'end', 'first_renovation_begin', 'first_renovation_end', 'second_renovation_begin', 'second_renovation_end', 'number', 'contract_file', 'contract_file_name', 'effective'];

    protected $casts = [
        'effective' => 'boolean',
    ];
}
