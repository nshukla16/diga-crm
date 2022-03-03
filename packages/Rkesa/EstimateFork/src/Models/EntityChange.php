<?php

namespace Rkesa\EstimateFork\Models;

use Illuminate\Database\Eloquent\Model;

class EntityChange extends Model
{
    protected $fillable = ['change_type', 'subject', 'price', 'quantity', 'correction', 'resource_id', 'ficha_id',
        'category', 'subcategory', 'description', 'note', 'estimate_unit_id', 'position', 'quantity_type'];

    public $timestamps = false;

    public function fork_entity()
    {
        return $this->belongsTo(ForkEntity::class);
    }

    public function resources()
    {
        return $this->hasMany(EntityChangeResource::class);
    }
}
