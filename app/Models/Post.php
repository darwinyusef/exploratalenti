<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\User;
use App\Entities\Files;
use App\Entities\Taxonomies;

class Post extends Model {
  use SoftDeletes;
  protected $fillable = [
    'title',	'content',	'slug',	'excerpt',	'password',	'url',	'type',	'state',	'time_in'	,'time_out',	'parent',	'user_id'
  ];

  public function getRouteKeyName(){
      return 'slug';
  }

  public function user(){
      return $this->belongsTo(User::class);
  }

  public function files() {
        return $this->morphToMany(Files::class, 'fileable');
  }

  public function taxonomy() {
        return $this->morphToMany(Taxonomies::class, 'taxonoable');
  }

}
