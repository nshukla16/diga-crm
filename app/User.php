<?php

namespace App;

use Auth;
use Carbon\Carbon;
use App\UserDevice;
use App\UserContract;
use App\Events\EventChannel;
use App\Number as ZadarmaNumber;
use Rkesa\Calendar\Models\Event;
use Rkesa\Hr\Models\UserDocument;
use Rkesa\Service\Models\Service;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Rkesa\Hr\Models\UserExperience;
use Rkesa\Hr\Models\KpiUserAndGroup;
use Illuminate\Support\Facades\Cache;
use Rkesa\Client\Models\ClientContact;
use Illuminate\Notifications\Notifiable;
use Rkesa\Hr\Http\Helpers\KpiPeriodsHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rkesa\Estimate\Models\EstimateGroupWorker;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'active', 'group_id', 'photo', 'widget_order', 'pin', 'sub', 'can_finish_projects'
    ];

    protected $casts = [
        'gc_enabled' => 'boolean',
        'can_see_prices' => 'boolean',
        'can_see_kpi' => 'boolean',
        'is_admin' => 'boolean',
        'new_client_notifications' => 'boolean',
        'new_fb_messages_notifications' => 'boolean',
        'salary_type' => 'boolean',
        'active' => 'boolean',
        'show_product_tour' => 'boolean',
        'email_suggestions' => 'boolean',
        'can_see_financial_calendar' => 'boolean',
        'can_export' => 'boolean',
        'can_give_discount' => 'boolean',
        'can_enter_timesheet_and_consumption' => 'boolean',
        'can_approve_vacations' => 'boolean',
        'show_calendar_on_main_page' => 'boolean',
        'autosave_estimates' => 'boolean',
        'can_finish_projects' => 'boolean'
    ];

    protected $dates = ['deleted_at'];

    protected $appends = ['groupmates_ids', 'roles', 'kpi'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'groupmates', 'email_password'
    ];

    public function receivesBroadcastNotificationsOn()
    {
        return EventChannel::pusher_user_channel($this->id);
    }

    public function user_experiences()
    {
        return $this->hasMany(UserExperience::class);
    }

    public function educ_before()
    {
        return $this->hasMany(UserExperience::class)->educ();
    }

    public function work_before()
    {
        return $this->hasMany(UserExperience::class)->work();
    }

    public function roles()
    {
        return $this->hasMany(Role::class)->select(['action', 'create', 'read', 'update', 'delete', 'addit']);
    }

    /* Roles caching */

    protected function get_roles_cache_key()
    {
        return 'user_' . $this->id . '_roles';
    }

    public function roles_cached()
    {
        return Cache::remember($this->get_roles_cache_key(), 10, function () {
            return $this->roles()->get()->toArray();
        });
    }

    public function clear_roles_cache(): bool
    {
        return Cache::forget($this->get_roles_cache_key());
    }

    /* Roles caching end */

    public function getRolesAttribute()
    {
        return $this->roles()->get();
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function events()
    {
        switch ($this->cando('events', 'read')) {
            case 0:
                return $this->hasMany(Event::class)->where('id', null);
            case 1:
                return $this->hasMany(Event::class)->where('user_id', $this->id);
            case 2:
                return $this->hasMany(Event::class)->whereIn('user_id', $this->groupmates_ids());
            case 3:
                return $this->hasMany(Event::class);
        }
    }

    public function cando($action, $type)
    {
        // experimental
        foreach ($this->roles_cached() as $role) {
            if ($role['action'] == $action) {
                return $role[$type];
            }
        }
        //        return Role::where(['user_id' => $this->id, 'action' => $action])->first()[$type];
    }

    public function set_permission($action, $type, $value)
    {
        $role = Role::where(['user_id' => $this->id, 'action' => $action])->first();
        $role[$type] = $value;
        $role->save();
    }

    public function set_admin($val)
    {
        $this->is_admin = $val;
        $this->save();
    }

    public function create_permissions()
    {
        foreach (User::get_role_models() as $action) {
            Role::create(['user_id' => $this->id, 'action' => $action]);
        }
    }

    public function groupmates()
    {
        return $this->hasMany(User::class, 'group_id', 'group_id');
    }

    public function groupmates_ids()
    {
        return $this->groupmates->pluck('id')->all();
    }

    public function getGroupmatesIdsAttribute()
    {
        return $this->groupmates_ids();
    }

    // Converting decimal to binary and returning true or false depending on the row
    public function can_with_project($type, $section)
    {
        $decimal = $this->cando('projects', $type);
        $bin = sprintf("%08d", decbin($decimal));
        return $bin[$section] == 1;
    }

    public function can_with_contact($type, $contact)
    {
        switch ($this->cando('clients', $type)) {
            case 0:
                return false;
            case 1:
                $event_exist = $contact->events()->where('user_id', $this->id)->count() > 0;
                $service_exist = $contact->services()->where('responsible_user_id', $this->id)->count() > 0;
                return $event_exist || $service_exist || ($type == 'read' && in_array($this->id, [1, 2]));
            case 2:
                $event_exist = $contact->events()->whereIn('user_id', $this->groupmates_ids())->count() > 0;
                $service_exist = $contact->services()->whereIn('responsible_user_id', $this->groupmates_ids())->count() > 0;
                return $event_exist || $service_exist || ($type == 'read' && in_array($this->id, [1, 2]));
            case 3:
                return true;
        }
    }

    public function can_with_client($type, $client)
    {
        switch ($this->cando('clients', $type)) {
            case 0:
                return false;
            case 1:
                $cc_ids = ClientContact::where('client_id', $client->id)->pluck('id');
                $event_exist = Event::whereIn('client_contact_id', $cc_ids)->where('user_id', $this->id)->count() > 0;
                $service_exist = Service::whereIn('client_contact_id', $cc_ids)->where('responsible_user_id', $this->id)->count() > 0;
                return $event_exist || $service_exist || ($type == 'read' && $this->id == 1);
            case 2:
                $cc_ids = ClientContact::where('client_id', $client->id)->pluck('id');
                $event_exist = Event::whereIn('client_contact_id', $cc_ids)->whereIn('user_id', $this->groupmates_ids())->count() > 0;
                $service_exist = Service::whereIn('client_contact_id', $cc_ids)->whereIn('responsible_user_id', $this->groupmates_ids())->count() > 0;
                return $event_exist || $service_exist || ($type == 'read' && $this->id == 1);
            case 3:
                return true;
        }
    }

    public function can_with_event($type, $event)
    {
        switch ($this->cando('events', $type)) {
            case 0:
                return false;
            case 1:
                return $event->user_id == $this->id;
            case 2:
                return in_array($event->user_id, $this->groupmates_ids());
            case 3:
                return true;
        }
    }
    ///*--project permissions-*/
    //    public function can_with_projects($type, $project)
    //    {
    //        switch($this->cando('projects', $type)) {
    //            case 0:
    //                return false;
    //            case 1:
    //                return $project->user_id == $this->id;
    //            case 2:
    //                return in_array($project->user_id, $this->groupmates_ids());
    //            case 3:
    //                return true;
    //        }
    //    }
    public function can_with_service($type, $service)
    {
        switch ($this->cando('services', $type)) {
            case 0:
                return false;
            case 1:
                return $service->responsible_user_id == $this->id;
            case 2:
                return in_array($service->responsible_user_id, $this->groupmates_ids());
            case 3:
                return true;
        }
    }

    public function can_with_estimate($type, $estimate)
    {
        switch ($this->cando('estimates', $type)) {
            case 0:
                return false;
            case 1:
                return $estimate->user_id == $this->id;
            case 2:
                return in_array($estimate->user_id, $this->groupmates_ids());
            case 3:
                return true;
        }
    }


    public function can_with_user($type, $user)
    {
        $current_user = Auth::user();
        if ($type == 'create' || $type == 'update' || $type == 'delete') {
            switch ($this->cando('users', $type)) {
                case 0:
                    return false;
                case 1:
                    return true;
            }
        }
        if ($type == 'read') {
            switch ($this->cando('users', $type)) {
                case 0:
                    return false;
                case 1:
                    return $user->group_id == $current_user->group_id;
                case 2:
                    return true;
            }
        }
    }

    //    public function can_with_entities($type, $estimate)
    //    {
    //        switch($this->cando('legal_entities', $type)) {
    //            case 0:
    //                return false;
    //            case 1:
    //                return $estimate->user_id == $this->id;
    //            case 2:
    //                return in_array($estimate->user_id, $this->groupmates_ids());
    //            case 3:
    //                return true;
    //        }
    //    }

    public static function users()
    {
        $current_user = Auth::user();
        switch ($current_user->cando('users', 'read')) {
            case 0:
                return User::where('id', null);
            case 1:
                return User::where('group_id', '<>', 0)->orWhere('id', $current_user->id);
            case 2:
                return User::query();
        }
    }

    public static function get_role_models()
    {
        return ['services', 'events', 'clients', 'estimates', 'fichas', 'resources', 'forks', 'plannings', 'users', 'projects', 'legal_entities', 'shipping_orders', 'expences', 'invoices'];
    }

    public function zadarma_number()
    {

        return $this->hasOne(ZadarmaNumber::class, 'internal_number', 'zadarma_internal_phonecode');
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

    public function chats()
    {
        return $this->hasManyThrough(
            Chat::class,
            ChatMember::class,
            'user_id',
            'id',
            'id',
            'chat_id'
        );
    }

    public function user_contracts()
    {
        return $this->hasMany(UserContract::class);
    }

    public function user_documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function estimate_group_workers()
    {
        return $this->hasMany(EstimateGroupWorker::class);
    }

    public function user_devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function user_salaries()
    {
        return $this->hasMany(UserSalary::class);
    }
}
