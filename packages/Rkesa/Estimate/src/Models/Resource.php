<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Resource extends Model
{
    protected $fillable = ['name', 'quantity', 'estimate_unit_id', 'price', 'resource_type', 'update_fichas', 'efficiency_estimate_unit_id'];

    public static function search($search, $type){
        return DB::table('resources')
            ->where('name', 'like', '%'.$search.'%')
            ->where('resource_type', '=', $type)
            ->get();
    }

    public function estimate_unit()
    {
        return $this->belongsTo(EstimateUnit::class);
    }

    public function resource_attachments()
    {
        return $this->hasMany(ResourceAttachment::class);
    }

    public function estimate_materials()
    {
        return $this->hasMany(EstimateMaterial::class);
    }
}
