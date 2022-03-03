<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\Resource;

class Product extends Model
{
    protected $fillable = ['name', 'quantity', 'estimate_unit_id', 'price', 'code', 'parent_id', 'resource_id', 'vat_type_id', 'category'];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function estimate_unit()
    {
        return $this->belongsTo(EstimateUnit::class);
    }

    public function vat_type()
    {
        return $this->belongsTo(VatType::class);
    }
}
