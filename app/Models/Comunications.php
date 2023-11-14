<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use App\Events\MenssajeCreated;


class Comunications extends Model
{
  use SoftDeletes;

  protected $table = 'comunications';

  protected $fillable = [
    'title',  'description',  'expiration',  'url',  'target',  'icon_class',  'color',  'progress',  'rol',  'location', 'user_id'
  ];

  static public $rules = [
    'title' => 'required|string|max:255',
    'description' => 'required|string|max:255',
    'url' => 'required|string|max:255',
    'location' => 'required|string|max:255',
  ];
}
