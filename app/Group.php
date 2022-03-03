<?php

namespace App;

use Carbon\Carbon;
use Rkesa\Hr\Models\KpiUserAndGroup;
use Rkesa\Service\Models\ServiceScope;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Client\Models\Client;
use Rkesa\Estimate\Models\EstimateGroup;
use Rkesa\Hr\Http\Helpers\KpiPeriodsHelper;

class Group extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'service_scope_id', 'head_user_id', 'type', 'client_id'];

    protected $appends = ['users_ids', 'kpi'];

    protected $hidden = ['users'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getUsersIdsAttribute()
    {
        return $this->users->pluck('id');
    }

    public function service_scope()
    {
        return $this->belongsTo(ServiceScope::class);
    }

    public function head_user()
    {
        return $this->belongsTo(User::class);
    }

    public function kpi_user_and_group()
    {
        return KpiUserAndGroup::where('user_id', '=', $this->id)->firstOr(function () {
            return null;
        });;
    }

    public function getKpiAttribute()
    {
        return $this->kpi();
    }

    public function kpi()
    {
        return $this->kpi_logic(Carbon::now());
    }

    public function period_kpi($endDate)
    {
        return $this->kpi_logic($endDate);
    }

    public function kpi_logic($endDate)
    {

        $ug = $this->kpi_user_and_group();
        if (isset($ug)) {
            $helper = new KpiPeriodsHelper;
            return $helper->kpi_logic($ug, $endDate);
        }
        return "";
    }

    public function estimate_groups()
    {
        return $this->hasMany(EstimateGroup::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
