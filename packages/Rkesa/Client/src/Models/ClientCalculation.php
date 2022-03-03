<?php

namespace Rkesa\Client\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCalculation extends Model
{
    protected $fillable = [
        'calculation_file_name',
        'calculation_file_path',
        'calculation_name',
        'client_id'
    ];
    //
}
