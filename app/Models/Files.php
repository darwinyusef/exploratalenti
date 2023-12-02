<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Post;
use App\Models\Slider;
use App\Models\Options;
use App\Models\Taxonomies;
use App\Models\Links;
use App\Models\Contents;
use App\Models\Courses;
use App\Models\Interaction;

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