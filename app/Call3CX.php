<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Call3CX extends Model
{
    protected $table = 'calls_3cx';

    protected $fillable = [
        'number',
        'call_type',
        'agent',
        'duration',
        'duration_minutes',
        'duration_seconds',
        'duration_milliseconds',
        'client_contact_id',
    ];
}
