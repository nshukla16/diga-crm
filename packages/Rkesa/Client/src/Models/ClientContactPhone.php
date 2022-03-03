<?php

namespace Rkesa\Client\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ClientContactPhone extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['client_contact_id', 'phone_number'];

    public function client_contact()
    {
        return $this->belongsTo(ClientContact::class);
    }
}
