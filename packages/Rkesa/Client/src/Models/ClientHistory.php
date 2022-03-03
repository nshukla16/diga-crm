<?php

namespace Rkesa\Client\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Site;
use Rkesa\Service\Models\Service;
use Rkesa\Service\Models\ServiceAttachment;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Calendar\Models\Event;
use App\Call;
use App\Call3CX;

class ClientHistory extends Model
{
    protected $table = 'client_history';

    protected $fillable = ['type_id', 'client_contact_id', 'event_id', 'user_id', 'call_id', 'message', 'call3cx_id'];

    public function client_contact()
    {
        return $this->belongsTo(ClientContact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->select('id', 'name');
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->select('id', 'estimate_number', 'created_at', 'address');
    }

    public function service_state()
    {
        return $this->belongsTo(ServiceState::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function service_attachment()
    {
        return $this->belongsTo(ServiceAttachment::class);
    }

    public function call()
    {
        return $this->hasOne(Call::class, 'id', 'call_id');
    }

    public function call_3cx()
    {
        return $this->hasOne(Call3CX::class, 'id', 'call3cx_id');
    }
}
