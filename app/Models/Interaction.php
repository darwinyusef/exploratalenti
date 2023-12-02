<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    protected $fillable = [
        'slug', 'interaction', 'response', 'content', 'value', 'force', 'rating', 'type',  'rol', 'notification'
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
