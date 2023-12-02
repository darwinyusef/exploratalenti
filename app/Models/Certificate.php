<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\BinaryUuid\HasBinaryUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Company;

class Certificate extends Model {
      use SoftDeletes;
      use HasBinaryUuid;

      protected $uuidSuffix = '_str';
      protected $uuids = [
        'uuid' // foreign or related key
      ];

      public static $rules = [
       'iduser' => 'required',
       'idcourse' => 'required',
       'value' => 'required',
       'email' => 'required',
       'cedula' => 'required',
       'firstname' => 'required',
       'lastname' => 'required'
      ];

      public function company(){
        return $this->belongsTo(Company::class);
      }

      protected $fillable = [ 'uuid',	'cedula',	'firstname',	'lastname',	'email',	'url',	'iduser', 'idcourse', 'value', 'configurates', 'status', 'othercompany', 'company_id'];
}
