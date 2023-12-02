<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Files;
use App\Models\Taxonomies;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'title',
        'content',
        'slug',
        'excerpt',
        'password',
        'url',
        'type',
        'state',
        'time_in',
        'time_out',
        'parent',
        'user_id',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
