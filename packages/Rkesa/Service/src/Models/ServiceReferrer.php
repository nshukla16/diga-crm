<?php

namespace Rkesa\Service\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReferrer extends Model
{
    protected $fillable = ['service_id', 'email', 'password', 'name', 'hash', 'email_sent_at', 'agree_to_receive_promotions', 'locale', 'formatted_cell_phone', 'tg_id'];
}
