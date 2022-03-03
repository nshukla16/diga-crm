<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rkesa\Client\Models\ClientHistory;
use Illuminate\Support\Facades\Schema;

class Call extends Model
{
    protected $table = 'zadarma_calls';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'caller_id',
        'called_did',
        'pbx_call_id',
        'call_id_with_rec',
        'call_start',
        'duration',
        'disposition',
        'is_recorded',
        'record_link',
        'record_link_lifetime_till'
    ];

    public function client_history()
    {
        return $this->belongsTo(ClientHistory::class);
    }

    public function getTableColumns()
    {
        return Schema::getColumnListing($this->table);
    }
}
