<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Files;
use App\Entities\Post;

class Taxonomies extends Model {
  use SoftDeletes;

  protected $fillable = [
    	'taxonomy',	'description','type', 'parent'
  ];

  public function files() {
        return $this->morphToMany(Files::class, 'fileable');
  }

  public function post(){
      return $this->morphedByMany(Post::class, 'taxonoable');
  }

}
