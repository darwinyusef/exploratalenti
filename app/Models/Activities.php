<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Activities extends Model {
  use SoftDeletes;
  protected $fillable = [ 'exam', 'icon', 'slug', 'synonymous', 'clinical_use', 'preparation', 'meaning', 'range', 'method','user_id' ];

  public function getRouteKeyName(){
      return 'slug';
  }

  public static $rules = [
   'exam'  => 'required',
   'synonymous'  => 'required',
   'clinical_use'  => 'required'
  ];

  public function files() {
    return $this->morphToMany(Files::class, 'fileable');
  }

  public function user(){
      return $this->belongsTo(User::class);
  }

}
