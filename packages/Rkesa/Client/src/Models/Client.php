<?php

namespace Rkesa\Client\Models;

//use Rkesa\Project\Models\ClientCalculation;

use App\Connection;
use App\GlobalSettings;
use App\Http\Traits\ConnectAdditionalFieldsTrait;
use Auth;
use Log;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Project\Models\ClientEquipment;
use Rkesa\Project\Models\Project;
use Rkesa\Service\Models\Service;
use Rkesa\Calendar\Models\Event;
use OwenIt\Auditing\Contracts\Auditable;

class Client extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use ConnectAdditionalFieldsTrait;

    protected $fillable = ['name', 'address_legal', 'address_mailing', 'nif', 'checking_account', 'correspondent_account', 'bic', 'phone', 'site', 'email', 'client_group', 'client_referrer_id', 'vip', 'referrer_note', 'note', 'a_attributes', 'is_group', 'connection_id', 'postal_code', 'city'];

    protected $appends = ['can_be_readed', 'can_be_updated', 'can_be_deleted', 'attributes_calculated'];

    protected $casts = [
        'a_attributes' => 'array',
        'is_group' => 'boolean',
    ];

    public function client_contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function client_referrer()
    {
        return $this->belongsTo(ClientReferrer::class);
    }

    public function main_contact()
    {
        return $this->client_contacts()->where('is_main_contact', true)->with('client_contact_phones')->first();
    }

    public function not_main_contact()
    {
        return $this->client_contacts()->where('is_main_contact', false)->with('client_contact_phones')->first();
    }

    public function getCanBeReadedAttribute()
    {
        $user = Auth::user();
        if ($user) {
            switch ($user->cando('clients', 'read')) {
                case 0:
                    return false;
                case 1:
                    $cc_ids = ClientContact::where('client_id', $this->id)->pluck('id');
                    $event_exist = Event::whereIn('client_contact_id', $cc_ids)->where('user_id', $user->id)->count() > 0;
                    $service_exist = Service::whereIn('client_contact_id', $cc_ids)->where('responsible_user_id', $user->id)->count() > 0;
                    return $event_exist || $service_exist;
                case 2:
                    $cc_ids = ClientContact::where('client_id', $this->id)->pluck('id');
                    $event_exist = Event::whereIn('client_contact_id', $cc_ids)->whereIn('user_id', $user->groupmates_ids())->count() > 0;
                    $service_exist = Service::whereIn('client_contact_id', $cc_ids)->whereIn('responsible_user_id', $user->groupmates_ids())->count() > 0;
                    return $event_exist || $service_exist;
                case 3:
                    return true;
            }
        } else {
            return false;
        }
    }

    public function getCanBeUpdatedAttribute()
    {
        $user = Auth::user();
        if ($user) {
            switch ($user->cando('clients', 'update')) {
                case 0:
                    return false;
                case 1:
                    $cc_ids = ClientContact::where('client_id', $this->id)->pluck('id');
                    $event_exist = Event::whereIn('client_contact_id', $cc_ids)->where('user_id', $user->id)->count() > 0;
                    $service_exist = Service::whereIn('client_contact_id', $cc_ids)->where('responsible_user_id', $user->id)->count() > 0;
                    return $event_exist || $service_exist;
                case 2:
                    $cc_ids = ClientContact::where('client_id', $this->id)->pluck('id');
                    $event_exist = Event::whereIn('client_contact_id', $cc_ids)->whereIn('user_id', $user->groupmates_ids())->count() > 0;
                    $service_exist = Service::whereIn('client_contact_id', $cc_ids)->whereIn('responsible_user_id', $user->groupmates_ids())->count() > 0;
                    return $event_exist || $service_exist;
                case 3:
                    return true;
            }
        } else {
            return false;
        }
    }

    public function getCanBeDeletedAttribute()
    {
        $user = Auth::user();
        if ($user && $this->id != 1) { // test company
            switch ($user->cando('clients', 'delete')) {
                case 0:
                    return false;
                case 1:
                    $cc_ids = ClientContact::where('client_id', $this->id)->pluck('id');
                    $event_exist = Event::whereIn('client_contact_id', $cc_ids)->where('user_id', $user->id)->count() > 0;
                    $service_exist = Service::whereIn('client_contact_id', $cc_ids)->where('responsible_user_id', $user->id)->count() > 0;
                    return $event_exist || $service_exist;
                case 2:
                    $cc_ids = ClientContact::where('client_id', $this->id)->pluck('id');
                    $event_exist = Event::whereIn('client_contact_id', $cc_ids)->whereIn('user_id', $user->groupmates_ids())->count() > 0;
                    $service_exist = Service::whereIn('client_contact_id', $cc_ids)->whereIn('responsible_user_id', $user->groupmates_ids())->count() > 0;
                    return $event_exist || $service_exist;
                case 3:
                    return true;
            }
        } else {
            return false;
        }
    }

    public function redirect_to_this_client()
    {
        $user = Auth::user();
        $events = Event::where('client_id', $this->id);
        if ($events->count() > 0) { // has done events
            $hist = ClientHistory::where(['type_id' => 18, 'client_id' => $this->id])->orderBy('created_at', 'desc')->first();
            if ($hist && $hist->event->user_id == $user->id) { // user done last task
                return true;
            }
        } else {
            if ($this->creator_user_id == $user->id) { // user created this client
                return true;
            }
        }
        return false;
    }

    public function getAdditionalFieldsAttribute()
    {
        return GlobalSettings::first()->client_attributes;
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function equipment()
    {
        return $this->hasMany(ClientEquipment::class);
    }

    public function calculations()
    {
        return $this->hasMany(ClientCalculation::class);
    }

    public function connection()
    {
        return $this->belongsTo(Connection::class);
    }
}
