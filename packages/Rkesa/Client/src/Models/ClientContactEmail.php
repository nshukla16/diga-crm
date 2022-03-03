<?php

namespace Rkesa\Client\Models;

use Illuminate\Database\Eloquent\Model;

class ClientContactEmail extends Model
{
    protected $fillable = ['client_contact_id', 'email'];

    public function client_contact()
    {
        return $this->belongsTo(ClientContact::class);
    }
}
