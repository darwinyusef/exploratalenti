<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\User;
use App\Entities\Post;
use App\Entities\Slider;
use App\Entities\Options;
use App\Entities\Taxonomies;
use App\Entities\Links;
use App\Entities\Contents;
use App\Entities\Courses;
use App\Entities\Interaction;

class Files extends Model {
    use SoftDeletes;
    protected $table = 'multimedia';
    protected $fillable = ['name', 'models', 'user_id', 'description', 'url', 'expiration', 'type_file', 'file_location'];
    protected $dates = ['deleted_at'];
    public $timestamps = true;

    public static $rules = [
     'all_files' => 'required|max:10000'
    ];
    public static $rules_nofile = [
      'all_files' => 'max:10000'
    ];

    public function post(){
        return $this->morphedByMany(Post::class, 'fileable');
    }

    public function user(){
        return $this->morphedByMany(User::class, 'fileable');
    }

    public function taxonomies(){
        return $this->morphedByMany(Taxonomies::class, 'fileable');
    }

    public function options(){
        return $this->morphedByMany(Options::class, 'fileable');
    }

    public function slider(){
        return $this->morphedByMany(Slider::class, 'fileable');
    }

    public function links(){
        return $this->morphedByMany(Links::class, 'fileable');
    }

    public function course(){
        return $this->morphedByMany(Courses::class, 'fileable');
    }

    public function content(){
        return $this->morphedByMany(Contents::class, 'fileable');
    }

    public function interaction(){
        return $this->morphedByMany(Interaction::class, 'fileable');
    }
}