<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class SpecificationEquipment extends Model
{
    protected $fillable = [
        'name', 'size', 'estimate_unit_id', 'model', 'vendor_code', 'count', 'equipment_id', 'manufacturer_id'
    ];

    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }
}
