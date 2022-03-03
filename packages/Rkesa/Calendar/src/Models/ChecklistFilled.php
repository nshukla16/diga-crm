<?php

namespace Rkesa\Calendar\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Service\Models\Service;

class ChecklistFilled extends Model
{
    protected $fillable = ['checklist_id', 'service_id'];

    public function checklist_filled_entities()
    {
        return $this->hasMany(ChecklistFilledEntity::class);
    }

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
