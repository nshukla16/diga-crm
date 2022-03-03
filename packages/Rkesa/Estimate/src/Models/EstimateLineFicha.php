<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EstimateLineFicha extends Model
{
    protected $fillable = ['name', 'description', 'estimate_unit_id', 'quantity', 'estimate_line_category_id', 'price', 'is_pattern'];

    public function lines()
    {
        return $this->morphMany(EstimateLine::class, 'lineable');
    }

    public function estimate_unit()
    {
        return $this->belongsTo(EstimateUnit::class);
    }

    public function category()
    {
        return $this->belongsTo(EstimateLineCategory::class);
    }

    public function resources()
    {
        return $this->hasMany(EstimateLineFichaResource::class);
    }

    public static function search($search){
        return DB::table('estimate_line_fichas')
            ->where('estimate_line_fichas.name', 'like', '%'.$search.'%')
            ->where('estimate_line_fichas.is_pattern', '=', true)
            ->get();
    }
}
