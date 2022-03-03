<?php

namespace Rkesa\Planning\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Estimate\Models\Estimate;

class UserPlanningUserTask extends Model
{
    public $fillable = [
        'user_planning_user_id',
        'start',
        'end',
        'title',
        'description',
        'color',
        'estimate_id',
        'notification_about_changes',
        'is_subcontract',
        'company_percent',
        'email_auto_send',
        'attach_invoice',
    ];

    protected $casts = [
        'is_subcontract' => 'boolean',
        'email_auto_send' => 'boolean',
        'attach_invoice' => 'boolean',
    ];

//    public function getEstimate(){
////        return Estimate::where('id', $this->estimate_id)->first();
////    }
//
////    protected $appends = ['estimate'];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
    public function gantt()
    {
        return $this->hasMany(EstimatePlanning::class, 'estimate_id', 'estimate_id');
    }

    public function user_planning_user()
    {
        return $this->belongsTo(UserPlanningUser::class);
    }
}
