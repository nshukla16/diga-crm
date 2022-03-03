<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Service\Models\ServiceReferrer;

class ChatMember extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service_referrer()
    {
        return $this->belongsTo(ServiceReferrer::class);
    }
}
