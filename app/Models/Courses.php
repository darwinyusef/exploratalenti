<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courses extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug', 'excerpt', 'course', 'payload', 'description', 'context', 'state', 'classroom', 'views', 'rating', 'level', 'descriptionTask', 'amountTask', 'timeOut', 'calification', 'subject', 'notification', 'send', 'meta', 'status', 'parent'
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
