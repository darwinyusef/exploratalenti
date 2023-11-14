<?php

namespace App\Entities;
use App\Entities\User;
use Illuminate\Database\Eloquent\Model;

class Makeuser extends Model {
    protected $fillable = [
      'archive',	'reasponse',	'type',	'active',	'user_id'
    ];

    public function user(){
      return $this->belongsTo(User::class, 'user_id');
    }
}
