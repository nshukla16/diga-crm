<?php

namespace Rkesa\EstimateFork\Models;

use Illuminate\Database\Eloquent\Model;

class EntityChangeResource extends Model
{
    protected $fillable = ['field', 'subject', 'resource_id'];

    public $timestamps = false;

    public function entity_change()
    {
        return $this->belongsTo(EntityChange::class);
    }
}
