<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Files;

class Options extends Model {
  use SoftDeletes;
  protected $fillable = [
      'option_key', 'option_value', 'settings', 'autoload', 'time_in', 'time_out', 'status'
    ];

    public function files() {
          return $this->morphToMany(Files::class, 'fileable');
    }
}
