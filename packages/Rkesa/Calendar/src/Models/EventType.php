<?php

namespace Rkesa\Calendar\Models;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $fillable = ['color', 'item', 'title', 'icon', 'show_description', 'order'];

    protected $casts = [
        'show_description' => 'boolean'
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
