<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon, Auth, Redirect, Session, Validator, Storage, File;
use App\Models\Files as MasterFiles;
use App\Http\Controllers\NotificationController as Notify; 

class HelperController extends Controller
{

  //changue date format
  static public function date_format_chargue($date)
  {
    $array = explode("/", $date);

    $finalDate = Carbon::create($array[2], $array[1], $array[0], '00', '00', '00');
    return $finalDate->toDateString();
  }


  public static function del_symbols($string)
  {

    $string = trim($string);

    $string = str_replace(
      array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
      array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
      $string
    );

    $string = str_replace(
      array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
      array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
      $string
    );

    $string = str_replace(
      array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
      array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
      $string
    );

    $string = str_replace(
      array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
      array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
      $string
    );

    $string = str_replace(
      array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
      array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
      $string
    );

    $string = str_replace(
      array('ñ', 'Ñ', 'ç', 'Ç'),
      array('n', 'N', 'c', 'C',),
      $string
    );

    $string = str_replace(
      array(
        "\\", "¨", "º", "-", "~",
        "#", "@", "|", "!", "\"",
        "·", "$", "%", "&", "/",
        "(", ")", "?", "'", "¡",
        "¿", "[", "^", "<code>", "]",
        "+", "}", "{", "¨", "´",
        ">", "< ", ";", ",", ":",
        ".", " "
      ),
      ' ',
      $string
    );
    return $string;
  }


  static public function chargueFile($input_name, $request)
  {
    $file = $request->file($input_name);
    $dt = Carbon::now();
    //se modifica el archivo colocando el día el mes el año el minuto y el segundo
    $carbon = $dt->day . $dt->month . $dt->year . $dt->hour . $dt->minute . $dt->second;
    //obtenemos el nombre del archivo
    $nombre = $file->getClientOriginalName();
    //indicamos que queremos guardar un nuevo archivo en el disco local
    \Storage::disk('local')->put($nombre,  \File::get($file));

    //verificamos si el archivo existe y lo retornamos
    $name = Auth::user()->id . $carbon . '.' . $file->getClientOriginalExtension();
    \Storage::move($nombre, 'ac' . $name);
    return $name;
  }


  static public function updateFile($input_name, $request)
  {
    $file = $request->file($input_name);
    $archivo = $request['imgOld'];

    if (isset($file)) {
      $dt = Carbon::now();
      //se modifica el archivo colocando el día el mes el año el minuto y el segundo
      $carbon = $dt->day . $dt->month . $dt->year . $dt->hour . $dt->minute . $dt->second;
      //obtenemos el nombre del archivo
      $nombre = $file->getClientOriginalName();
      //indicamos que queremos guardar un nuevo archivo en el disco local
      \Storage::disk('local')->put($nombre,  \File::get($file));
      //verificamos si el archivo existe y lo retornamos
      $name = Auth::user()->id . $carbon . '.' . $file->getClientOriginalExtension();
      \Storage::move($nombre, 'ac' . $name);
      /** se valida que existe el archivo con info old */
      if (Storage::exists(str_replace_first('files/', '', $archivo)) && $archivo != null) {

        $oldfile = MasterFiles::where('url', 'LIKE', "%" . $archivo . "%")->first();
        Storage::delete(str_replace_first('files/', '', $oldfile->url));
        $oldfile->forceDelete();
      }
    }
    return $name;
  }

  static public function deleteFile($request)
  {
    $archivo = $request['url'];
    if (Storage::exists(str_replace_first('files/', '', $archivo)) && $archivo != null) {
      Storage::delete(str_replace_first('files/', '', $archivo));
      $master = MasterFiles::findOrFail($request['id']);
      $master->forceDelete();
    }
  }
}
