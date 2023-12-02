<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Options extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'option_key',
        'option_value',
        'settings',
        'autoload',
        'time_in',
        'time_out',
        'status',
    ];

    public function files()
    {
        return $this->morphToMany(Files::class, 'fileable');
    }
}
