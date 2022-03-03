<?php

namespace Rkesa\Estimate\Models;

use App\Group;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Service\Models\Service;

class EstimateGroup extends Model
{
    protected $fillable = ['group_id', 'estimate_id', 'percent', 'is_subcontract', 'service_id', 'work_start', 'work_end', 'contractor_status', 'contractor_file', 'contractor_file_name'];

    protected $casts = [
        'is_subcontract' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function estimate_group_pay_stages()
    {
        return $this->hasMany(EstimateGroupPayStage::class);
    }
}
