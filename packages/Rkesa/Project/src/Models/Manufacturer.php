<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Rkesa\Project\Models\ProjectManufacturer;

class Manufacturer extends Model
{
    protected $fillable = ['name', 'legal_address', 'uploading_address', 'bank_name',
                'bic', 'checking_account', 'correspondent_account'];

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    public function contacts()
    {
        return $this->hasMany(ManufacturerContact::class);
    }

    public function contracts()
    {
        return $this->hasMany(ManufacturerContract::class);
    }

    public function additional_contracts()
    {
        return $this->hasMany(ManufacturerAdditionalContract::class);
    }

    public function orders()
    {
        return $this->hasMany(ManufacturerActualOrder::class);
    }

    public function projects_manufacturers()
    {
        return $this->hasMany(ProjectManufacturer::class);
    }
}
