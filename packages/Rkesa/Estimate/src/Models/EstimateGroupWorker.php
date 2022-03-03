<?php

namespace Rkesa\Estimate\Models;

use App\User;
use App\AuthPhoto;
use App\Branch;
use App\EstimateGroupWorkerActivity;
use Rkesa\Service\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\EstimateLineCategory;

class EstimateGroupWorker extends Model
{
    protected $fillable = [
        'estimate_group_id', 'user_id', 'date',
        'date_start_before_lunch', 'date_end_before_lunch', 'date_start_after_lunch', 'date_end_after_lunch',
        'estimate_line_category_id', 'estimate_unit_id', 'quantity',
        'estimate_id', 'is_suspicious', 'is_approved', 'service_id', 'client_contact_id', 'branch_id'
    ];

    protected $casts = [
        'paid' => 'boolean',
    ];

    public function estimate_group()
    {
        return $this->belongsTo(EstimateGroup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estimate_line_category()
    {
        return $this->belongsTo(EstimateLineCategory::class);
    }

    public function estimate_unit()
    {
        return $this->belongsTo(EstimateUnit::class);
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function client_contact()
    {
        return $this->belongsTo(ClientContact::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function auth_photos()
    {
        return $this->hasMany(AuthPhoto::class)->orderBy('created_at');
    }

    public function estimate_group_workers_activities()
    {
        return $this->hasMany(EstimateGroupWorkerActivity::class);
    }
}
