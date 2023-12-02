<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DataUser;
use App\Models\Certificate;

class Company extends Model
{
    protected $fillable = [ 'company',	'nit', 'parent', 'other'];

    public function dataUser(){
        return $this->hasMany(DataUser::class);
    }

    public function certificate(){
        return $this->hasMany(Certificate::class);
    }
}
