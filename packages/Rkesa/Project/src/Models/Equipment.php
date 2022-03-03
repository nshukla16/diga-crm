<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = ['name', 'size', 'model', 'vendor_code', 'manufacturer_id', 'estimate_unit_id'];

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }
}
