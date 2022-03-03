<?php

namespace Rkesa\Service\Models;

use Illuminate\Database\Eloquent\Model;

class AruPolygon extends Model
{
    protected $table = 'ARU_polygons';

    public function regions()
    {
        return $this->belongsTo(Aru::class);
    }



}
