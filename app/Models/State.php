<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class State extends Model
{
    use SoftDeletes;
    protected $table = 'state';
    protected $fillable = [ 'key', 'value', 'stateable', 'status', 'user_id', 'description'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public static $rules = [
        'key' => 'required',
        'description' => 'required',
        'status' => 'required',
        'user_id' => 'required'
    ];

}
