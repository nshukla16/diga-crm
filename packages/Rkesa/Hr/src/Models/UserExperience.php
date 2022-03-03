<?php

namespace Rkesa\Hr\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class UserExperience extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'begin', 'end', 'description', 'place', 'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include work experience
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWork($query)
    {
        return $query->where('type', '=', 1);
    }

    public function scopeEduc($query)
    {
        return $query->where('type', '=', 2);
    }
}
