<?php

namespace Rkesa\EstimateFork\Models;

use Illuminate\Database\Eloquent\Model;

class ForkEntity extends Model
{
    protected $fillable = ['object', 'order', 'category', 'subcategory'];

    public $timestamps = false;

    public function entity_rules()
    {
        return $this->hasMany(EntityRule::class);
    }

    public function entity_changes()
    {
        return $this->hasMany(EntityChange::class);
    }

    public function estimate_fork()
    {
        return $this->belongsTo(EstimateFork::class);
    }

}
