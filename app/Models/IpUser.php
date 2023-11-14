<?php

namespace App\Entities;
use App\Entities\User;

use Illuminate\Database\Eloquent\Model;

class IpUser extends Model {
  protected $fillable = [
    'ip',	'users_id'
  ];

  public function user(){
    return $this->belongsTo(User::class, 'user_id');
  }
}
