<?php

namespace App\Entities;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\DataUser;
use App\Entities\Post;
use App\Entities\State;
use App\Entities\Files;

class User extends Authenticatable {
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'status', 'validate_token', 'active', 'slug', 'display_name', 'city', 'type', 'theme', 'pago'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];


    static public $rules = [
      'first' => 'required|string|max:255',
      'last' => 'required|string|max:255',
      'card_id' => 'required|numeric|unique:data_users',
      'email' => 'required|string|email|max:255|unique:users',
      'city' => 'required|string|max:255',
      'password' => 'required|min:6|confirmed',
      'password_confirmation' => 'required|min:6'
    ];

    static public $rulesUpdate = [
      'first' => 'required|string|max:255',
      'last' => 'required|string|max:255',
      'email' => 'required|string|email|max:255',
    ];

    public function datauser(){
        return $this->hasMany(DataUser::class);
    }

    public function post(){
        return $this->hasMany(Post::class);
    }

    public function state(){
        return $this->hasMany(State::class);
    }

    public function files() {
          return $this->morphToMany(Files::class, 'fileable');
    }
}
