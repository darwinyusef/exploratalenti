<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Files;

class Slider extends Model {
  use SoftDeletes;

  protected $fillable = [
    'name',	'parameters',	'settings',	'layers',	'type',	'time_in',	'time_out',	'status'
  ];

  public function files() {
        return $this->morphToMany(Files::class, 'fileable');
  }
}
