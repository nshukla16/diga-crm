<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Number extends Model
{
    public $timestamps = false;

    protected $table = 'zadarma_numbers';
    
    public function user()
    {
        return $this->belongsTo(User::class, 'number', 'zadarma_internal_phonecode');
    }
}
