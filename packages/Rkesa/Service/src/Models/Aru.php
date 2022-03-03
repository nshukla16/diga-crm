<?php

namespace Rkesa\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Aru extends Model
{
    protected $table = 'ARU';

    public function coords()
    {
        return $this->hasMany(AruPolygon::class)->select(['aru_id', DB::raw('X(coordinates) as lat, Y(coordinates) as lng')]);
    }
    
}
