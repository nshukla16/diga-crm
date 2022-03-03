<?php

namespace Rkesa\Hr\Models;

use App\User;
use App\Group;
use Rkesa\Hr\Models\KpiPeriod;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Hr\Models\KpiUserAndGroupType;
use Rkesa\Hr\Http\Helpers\KpiPeriodsHelper;

class KpiUserAndGroup extends Model
{
    protected $table = 'kpi_users_and_groups';

    protected $fillable = ['user_id', 'group_id', 'period_id', 'start_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function period()
    {
        return $this->belongsTo(KpiPeriod::class);
    }

    public function types()
    {
        return $this->hasMany(KpiUserAndGroupType::class);
    }

    public function get_kpi($startDate, $endDate)
    {
        $tps = $this->types()->get();
        
        $helper = new KpiPeriodsHelper;

        if ($this->user_id != null){
            return $helper->kpi_productivity($startDate, $endDate, $tps, [$this->user_id]);
        }

        if ($this->group_id != null){
            $user_ids = $this->group()->users_ids;
            return $helper->kpi_productivity($startDate, $endDate, $tps, $user_ids);
        }        

        return 0;
    }
}
