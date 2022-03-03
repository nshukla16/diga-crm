<?php

namespace Rkesa\Service\Models;

use App\ContractorServicePayStage;
use App\User;
use App\GlobalSettings;
use Rkesa\Calendar\Models\Event;
use Rkesa\Project\Models\Project;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimateGroup;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientHistory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Rkesa\Calendar\Models\ChecklistFilled;
use Illuminate\Notifications\DatabaseNotification;

class Service extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['name', 'note', 'address', 'autocomplete_disabled', 'service_state_id', 'responsible_user_id', 'service_priority_id', 'service_type_id', 'client_contact_id', 'aru_id', 'estimate_summ', 'paid_summ', 'master_estimate_id', 'additional', 'access_enabled', 'platform_id'];

    protected $appends = ['has_estimate', 'project_id'];

    protected $hidden = ['project'];

    protected $casts = [
        'access_enabled' => 'boolean'
    ];


    public function getHasEstimateAttribute()
    {
        return Estimate::where(array('service_id' => $this->id, 'revision' => null, 'option' => null))->count() > 0;
    }

    public function getProjectIdAttribute()
    {
        return $this->project ? $this->project->id : null;
    }

    public function delete()
    {
        DatabaseNotification::whereRaw('JSON_EXTRACT(data, "$.service_id") = ' . $this->id)->delete();
        return parent::delete();
    }

    public function responsible_user()
    {
        return $this->belongsTo(User::class);
    }

    public function client_contact()
    {
        return $this->belongsTo(ClientContact::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function active_events()
    {
        return $this->hasMany(Event::class)->active();
    }

    public function service_state()
    {
        return $this->belongsTo(ServiceState::class);
    }

    public function service_priority()
    {
        return $this->belongsTo(ServicePriority::class);
    }

    public function service_type()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function estimates()
    {
        return $this->hasMany(Estimate::class);
    }

    public function region()
    {
        return $this->hasOne(Aru::class, 'id', 'aru_id');
    }

    public function attachments()
    {
        return $this->hasMany(ServiceAttachment::class);
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }

    public function checklist_filleds()
    {
        return $this->hasMany(ChecklistFilled::class);
    }

    public function generate_estimate_number()
    {
        $gs = GlobalSettings::first();
        $gs->last_estimate_number++;
        $gs->save();
        $this->estimate_number = $gs->last_estimate_number . '-' . date("y");
        return $this->estimate_number;
    }

    public function get_service_number()
    {
        return $this->estimate_number . ($this->additional != "" ? " " . trans('template.additional') . $this->additional : "");
    }

    public function client_history()
    {
        return $this->hasMany(ClientHistory::class);
    }

    public function groups()
    {
        return $this->hasMany(EstimateGroup::class, 'service_id');
    }

    public function contractor_service_pay_stages()
    {
        return $this->hasMany(ContractorServicePayStage::class);
    }

    public function estimate()
    {
        return $this->hasOne(Estimate::class, 'id', 'master_estimate_id');
    }
}
