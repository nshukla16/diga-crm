<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectNotification extends Model
{
    protected $fillable = ['type'];

    public function recipients()
    {
        return $this->hasMany(ProjectNotificationRecipient::class);
    }
}
