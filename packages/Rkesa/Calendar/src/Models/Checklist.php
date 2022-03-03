<?php

namespace Rkesa\Calendar\Models;

use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Calendar\Models\ChecklistArea;
use Rkesa\Calendar\Models\ChecklistWork;

class Checklist extends Model
{
    use ValidatingTrait;
    protected $throwValidationExceptions = true;

    protected $rules = [
        'name'   => 'required|unique:checklists,name'
    ];

    protected $fillable = ['name', 'description', 'footer'];

    public function checklist_entities()
    {
        return $this->hasMany(ChecklistEntity::class)->orderBy('order');
    }

    public function checklist_works()
    {
        return $this->hasMany(ChecklistWork::class)->orderBy('order');
    }

    public function checklist_areas()
    {
        return $this->hasMany(ChecklistArea::class)->orderBy('order');
    }
}
