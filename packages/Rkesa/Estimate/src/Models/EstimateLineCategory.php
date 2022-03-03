<?php

namespace Rkesa\Estimate\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateLineCategory extends Model
{
    public $timestamps = false;

    public function lines()
    {
        return $this->morphMany(EstimateLine::class, 'lineable');
    }

    public function parent()
    {
        return $this->belongsTo(EstimateLineCategory::class, 'category_id');
    }

    public function children()
    {
        return $this->hasMany(EstimateLineCategory::class);
    }

    public function fichas()
    {
        return $this->hasMany(EstimateLineFicha::class);
    }
}
