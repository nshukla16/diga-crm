<?php

namespace Rkesa\Client\Models;

use App\User;
use Auth;
use App\GlobalSettings;
use App\Http\Traits\ConnectAdditionalFieldsTrait;
use Illuminate\Database\Eloquent\Model;
use Rkesa\Calendar\Models\Event;
use Rkesa\Service\Models\Service;
use OwenIt\Auditing\Contracts\Auditable;

class ClientContact extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use ConnectAdditionalFieldsTrait;

    protected $fillable = ['name', 'surname', 'sex', 'profession', 'note', 'nif', 'contact_type', 'is_main_contact', 'client_id', 'client_referrer_id', 'referrer_note', 'a_attributes', 'responsible_user_id', 'address', 'postal_code', 'city'];

    protected $appends = ['can_be_readed', 'can_be_updated', 'can_be_deleted', 'attributes_calculated'];

    protected $casts = [
        'a_attributes' => 'array',
        'is_main_contact' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function services()
    {
        $user = Auth::user();
        switch ($user->cando('services', 'read')) {
            case 0:
                return $this->hasMany(Service::class)->where('id', null);
            case 1:
                return $this->hasMany(Service::class)->where('responsible_user_id', $user->id);
            case 2:
                return $this->hasMany(Service::class)->whereIn('responsible_user_id', $user->groupmates_ids());
            case 3:
                return $this->hasMany(Service::class);
        }
    }

    public function events()
    {
        $user = Auth::user();
        switch ($user->cando('events', 'read')) {
            case 0:
                return $this->hasMany(Event::class)->where('id', null);
            case 1:
                return $this->hasMany(Event::class)->where('user_id', $user->id);
            case 2:
                return $this->hasMany(Event::class)->whereIn('user_id', $user->groupmates_ids());
            case 3:
                return $this->hasMany(Event::class);
        }
    }

    public function active_events()
    {
        return $this->hasMany(Event::class)->active();
    }


    public function getCanBeReadedAttribute()
    {
        $user = Auth::user();
        if ($user) {
            switch ($user->cando('clients', 'read')) {
                case 0:
                    return false;
                case 1:
                    $event_exist = $this->events()->where('user_id', $user->id)->count() > 0;
                    $service_exist = $this->services()->where('responsible_user_id', $user->id)->count() > 0;
                    return $event_exist || $service_exist;
                case 2:
                    $event_exist = $this->events()->whereIn('user_id', $user->groupmates_ids())->count() > 0;
                    $service_exist = $this->services()->whereIn('responsible_user_id', $user->groupmates_ids())->count() > 0;
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
                    $event_exist = $this->events()->where('user_id', $user->id)->count() > 0;
                    $service_exist = $this->services()->where('responsible_user_id', $user->id)->count() > 0;
                    return $event_exist || $service_exist;
                case 2:
                    $event_exist = $this->events()->whereIn('user_id', $user->groupmates_ids())->count() > 0;
                    $service_exist = $this->services()->whereIn('responsible_user_id', $user->groupmates_ids())->count() > 0;
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
        if ($user && $this->id != 1  && $this->id != 2) { // test contacts
            switch ($user->cando('clients', 'delete')) {
                case 0:
                    return false;
                case 1:
                    $event_exist = $this->events()->where('user_id', $user->id)->count() > 0;
                    $service_exist = $this->services()->where('responsible_user_id', $user->id)->count() > 0;
                    return $event_exist || $service_exist;
                case 2:
                    $event_exist = $this->events()->whereIn('user_id', $user->groupmates_ids())->count() > 0;
                    $service_exist = $this->services()->whereIn('responsible_user_id', $user->groupmates_ids())->count() > 0;
                    return $event_exist || $service_exist;
                case 3:
                    return true;
            }
        } else {
            return false;
        }
    }

    public function client_history()
    {
        return $this->hasMany(ClientHistory::class)->orderBy('created_at', 'desc');
    }

    public function client_contact_phones()
    {
        return $this->hasMany(ClientContactPhone::class);
    }

    public function client_referrer()
    {
        return $this->belongsTo(ClientReferrer::class);
    }

    public function client_contact_emails()
    {
        return $this->hasMany(ClientContactEmail::class);
    }

    public function getAdditionalFieldsAttribute()
    {
        return GlobalSettings::first()->contact_attributes;
    }

    public function getNameWithLinkAttribute()
    {
        return '<a href="/contacts/' . $this->id . '">' . $this->name . ' ' . $this->surname . '</a>';
    }

    public function responsible_user()
    {
        return $this->belongsTo(User::class);
    }
}
