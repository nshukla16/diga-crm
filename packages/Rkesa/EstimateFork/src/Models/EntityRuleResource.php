<?php

namespace Rkesa\EstimateFork\Models;

use Illuminate\Database\Eloquent\Model;

class EntityRuleResource extends Model
{
    protected $fillable = ['field', 'rule_type', 'subject', 'resource_id'];

    public $timestamps = false;
}
