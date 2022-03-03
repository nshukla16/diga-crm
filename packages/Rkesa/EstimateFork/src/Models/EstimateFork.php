<?php

namespace Rkesa\EstimateFork\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateFork extends Model
{
    public function fork_entities()
    {
        return $this->hasMany(ForkEntity::class)->orderBy('order');
    }

}
