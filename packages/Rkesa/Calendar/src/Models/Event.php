<?php

namespace Rkesa\Calendar\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Notifications\DatabaseNotification;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Project\Models\Project;
use Watson\Validating\ValidatingTrait;
use Rkesa\Service\Models\Service;
use Rkesa\Project\Models\Manufacturer;

class Event extends Model
{
    use ValidatingTrait;
    protected $throwValidationExceptions = true;

    protected $rules = [
        'start'   => 'required',
        'event_type_id' => 'required|not_in:0'
    ];

    protected $fillable = ['user_id', 'event_type_id', 'start', 'finish', 'description', 'creator_user_id', 'client_contact_id', 'service_id', 'project_id', 'done', 'event_group_id', 'show_notification', 'url'];

    protected $dates = ['start', 'finish'];

    public function delete()
    {
        DatabaseNotification::whereRaw('JSON_EXTRACT(data, "$.event_id") = '.$this->id)->delete();
        return parent::delete();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client_contact()
    {
        return $this->belongsTo(ClientContact::class);
    }

    public function event_type()
    {
        return $this->belongsTo(EventType::class);
    }

    public function creator_user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('done', 0);
    }
}
