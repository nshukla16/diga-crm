<?php

namespace Rkesa\EstimateFork\Models;

use Rkesa\EstimateFork\Models\EntityRuleResource;
use Illuminate\Database\Eloquent\Model;

class EntityRule extends Model
{
    protected $fillable = ['field', 'rule_type', 'subject'];

    public $timestamps = false;

    public function fork_entity()
    {
        return $this->belongsTo(ForkEntity::class);
    }

    public function resources()
    {
        return $this->hasMany(EntityRuleResource::class);
    }
}
