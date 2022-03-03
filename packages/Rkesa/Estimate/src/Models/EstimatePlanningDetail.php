<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class EstimatePlanningDetail extends Model
{
    protected $fillable = ['estimate_id', 'days', 'start_point_lat', 'start_point_lng', 'consumption_per_100_km', 'gasoline_price', 'company_percent'];

}