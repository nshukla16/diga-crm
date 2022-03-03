<?php

namespace Rkesa\Hr\Models;

use Illuminate\Database\Eloquent\Model;

class KpiUserAndGroupType extends Model
{
    protected $table = 'kpi_users_and_groups_types';

    protected $fillable = ['type_id', 'plan_amount', 'additional_params', 'kpi_user_and_group_id'];

    public function type()
    {
        return $this->belongsTo(KpiType::class);
    }
}
