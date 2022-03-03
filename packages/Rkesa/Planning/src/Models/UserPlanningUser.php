<?php

namespace Rkesa\Planning\Models;

use App\Group;
use Illuminate\Database\Eloquent\Model;

class UserPlanningUser extends Model
{
    protected $fillable = ['user_id', 'user_planning_id', 'color', 'content'];

    public function tasks()
    {
        return $this->hasMany(UserPlanningUserTask::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'user_id', 'id');
    }
}
