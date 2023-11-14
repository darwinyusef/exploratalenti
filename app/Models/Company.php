<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Entities\DataUser;
use App\Entities\Certificate;

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
