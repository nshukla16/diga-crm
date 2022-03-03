<?php

namespace Rkesa\Estimate\Models;

use App\User;
use Rkesa\Estimate\Models\Resource;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\EstimateLineCategory;

class EstimateGroupMaterialConsumption extends Model
{
    protected $table = 'estimate_group_materials_consumption';

    protected $fillable = ['estimate_group_id', 'date', 'estimate_line_category_id', 'resource_id', 'estimate_unit_id', 'quantity'];

    public function estimate_group()
    {
        return $this->belongsTo(EstimateGroup::class);
    }

    public function estimate_line_category()
    {
        return $this->belongsTo(EstimateLineCategory::class);
    }

    public function estimate_unit()
    {
        return $this->belongsTo(EstimateUnit::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
