<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    protected $fillable = [
        'start',
        'end',
        'user_id',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
