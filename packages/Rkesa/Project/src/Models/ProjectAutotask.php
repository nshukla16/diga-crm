<?php

namespace Rkesa\Project\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAutotask extends Model
{
    protected $fillable = ['type'];

    public function recipients()
    {
        return $this->hasMany(ProjectAutotaskRecipient::class);
    }
}
