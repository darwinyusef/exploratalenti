<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Makeuser extends Model {
    protected $fillable = [
      'archive',	'reasponse',	'type',	'active',	'user_id'
    ];

    public function user(){
      return $this->belongsTo(User::class, 'user_id');
    }
}
