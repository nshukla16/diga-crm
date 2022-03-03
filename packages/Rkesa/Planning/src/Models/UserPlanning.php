<?php

namespace Rkesa\Planning\Models;

use Illuminate\Database\Eloquent\Model;
//use Rkesa\Estimate\Models\Estimate;

class UserPlanning extends Model
{
//    protected $fillable = ['name', 'is_custom'];

    public function users()
    {
        return $this->hasMany(UserPlanningUser::class);
    }

}
