<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class ClientEquipment extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'size',
        'model',
        'estimate_unit_id',
        'vendor_code',
        'manufacturer_id',
        'manufacturer_name',
    ];

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }
}
