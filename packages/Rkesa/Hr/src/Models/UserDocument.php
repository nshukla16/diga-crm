<?php

namespace Rkesa\Hr\Models;

use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    protected $fillable = ['user_id', 'type', 'file', 'file_name'];

}
