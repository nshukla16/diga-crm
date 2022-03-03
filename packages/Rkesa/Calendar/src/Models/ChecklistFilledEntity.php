<?php

namespace Rkesa\Calendar\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistFilledEntity extends Model
{
    public function checklist_entity()
    {
        return $this->belongsTo(ChecklistEntity::class);
    }
}
