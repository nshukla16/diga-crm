<?php

namespace Rkesa\Project\Models;

use App\Group;
use App\User;
use Illuminate\Database\Eloquent\Model;

class ProjectNotificationRecipient extends Model
{
    protected $fillable = ['type', 'group_id', 'user_id'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
