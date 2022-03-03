<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class LegalEntityContract extends Model
{
    protected $fillable = [
        'name',
        'file',
        'file_name',
        'legal_entity_id'
    ];
}
