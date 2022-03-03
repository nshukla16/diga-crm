<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
    protected $fillable = [
        'name', 'notes'
    ];

    public function equipment()
    {
        return $this->hasMany(SpecificationEquipment::class);
    }
}
