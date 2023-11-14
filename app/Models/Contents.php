<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contents extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'content', 'description', 'slug', 'password', 'payload', 'value', 'rating', 'excerpt', 'view', 'order', 'urlInbox', 'timeIn', 'timeOut', 'type', 'rol', 'assing', 'classroom', 'classroomText', 'address', 'timeLine', 'send', 'status', 'parent', 'deleted_at'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function files()
    {
        return $this->morphToMany(Files::class, 'fileable');
    }

    public function taxonomy()
    {
        return $this->morphToMany(Taxonomies::class, 'taxonoable');
    }
}
