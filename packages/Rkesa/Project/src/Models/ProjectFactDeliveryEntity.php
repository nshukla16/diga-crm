<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFactDeliveryEntity extends Model
{
    protected $fillable = ['exist', 'date', 'notes'];

    protected $casts = [
        'exist' => 'boolean'
    ];
}
