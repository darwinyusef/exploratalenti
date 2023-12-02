<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Links extends Model {
    protected $fillable = [ 'url', 'name', 'icon', 'location','target', 'description', 'visible', 'notes', 'parent_id' ];

    public function files() {
      return $this->morphToMany(Files::class, 'fileable');
    }
}
