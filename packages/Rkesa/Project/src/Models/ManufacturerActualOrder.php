<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Client\Models\Client;

class ManufacturerActualOrder extends Model
{
    protected $fillable = [
        'manufacturer_id',
        'name',
        'client_id',
        'contract_number',
        'specification_number',
        'file',
        'file_name'
    ];

    protected $appends = ['client_option'];

    protected $hidden = ['client'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getClientOptionAttribute()
    {
        return ['id' => $this->client->id, 'name' => $this->client->name];
    }
}
