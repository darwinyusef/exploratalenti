<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Company;


class DataUser extends Model
{
  use SoftDeletes;
  protected $fillable = [
    'id', 'first', 'last', 'birth', 'card_id', 'type_card', 'gender', 'mobile', 'phone_home',
    'address', 'neighborhood', 'postcode',  'city_id',  'user_id', 'company_id' , 'typecourse', 'course_id' ];

  protected $dates = ['deleted_at'];

  public function user(){
    return $this->belongsTo(User::class);
  }

  public function company(){
    return $this->belongsTo(Company::class);
  }

}
