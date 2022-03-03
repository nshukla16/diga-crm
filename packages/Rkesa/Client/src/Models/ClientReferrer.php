<?php

namespace Rkesa\Client\Models;

use Illuminate\Database\Eloquent\Model;

class ClientReferrer extends Model
{
    public $timestamps = false;

    protected $fillable = ['title'];

    public function client()
    {
        return $this->hasMany(Client::class);
    }
}
