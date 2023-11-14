<?php

namespace App\Http\Controllers;

use App\Entities\Files as Multimedia;
use Illuminate\Http\Request;
use App\Http\Controllers\helperController as Help;
use App\Entities\User;
use App\Entities\Post;
use App\Entities\Slider;
use App\Entities\Options;
use App\Entities\Taxonomies;
use App\Entities\Categories;

use App\Entities\Courses;
use App\Entities\Contents;
use App\Entities\Interaction;

use App\Entities\Links;
use App\Entities\Laboratories;



use Eloquent, Session, DB, Carbon\Carbon, Auth;

class MultimediaController extends Controller
{

  public function __construct()
  {
    $this->middleware(['permission:file.update'])->only(['update', 'edit']);
    $this->middleware(['permission:file.destroy'])->only(['destroy']);
    $this->middleware('auth');
  }

  public function index(Request $request)
  {

    $paginate = 20;
    $files = Auth::user()->files;

    if (Auth::user()->hasAnyPermission('file.list')) {
      $files = DB::table('fileables')->where('fileable_type', '<>', 'App\Entities\User')->leftJoin('multimedia', 'multimedia.id', '=', 'files_id')
        ->select('multimedia.created_at', 'multimedia.id', 'type_file', 'url', 'name')->paginate($paginate);
    }

    if (Auth::user()->hasAnyPermission('files')) {
      $files = Multimedia::paginate($paginate);
    }
    if (isset($request)) {
      $files = Multimedia::where('name', 'LIKE', '%' . $request->file . '%')->paginate($paginate);
    }



    return view('backend.admin.files', compact('files'));
  }

  public function store(Request $request)
  {
    $input_name = 'all_files';
    $file_name =  Help::chargueFile($input_name, $request);
    $file = $request->file($input_name);
    $model = $request->model;

    Eloquent::unguard();
    $archivo =  [
      'name' => $request->all()['name_file'],
      'url' => 'files/' . 'ac' . $file_name,
      'description' => $request->all()['description_file'],
      'type_file' => $file->getClientOriginalExtension(),
      //Elimninar
      'file_location' =>  env('APP_URL') . '/files/' . 'ac' . $file_name,
      'models' => $model,
      'user_id' => Auth::user()->id
    ];
    $bdFile = Multimedia::create($archivo);
    if ($model != null) {
      $consult = ['file' => $bdFile->id, 'id' => $request->id_model, 'model' => $model];
      $this->file_assign(null, $consult);
    }
    Session::flash('snackbar-success', 'Archivo creado correctamente');
    return back();
  }

  public function edit($id)
  {
    $files = Multimedia::where('id', $id)->first();
    return view('backend.admin.files_edit', compact('files'));
  }

  public function update(Request $request, $id)
  {
    $input_name = 'all_files';
    if (empty($request->file($input_name))) {
      $solicitud = $request->toArray();
      $solicitud = array_except($solicitud, [$input_name]);
      $solicitud = array_except($solicitud, ["_token"]);
      $solicitud = array_except($solicitud, ["_method"]);
      $solicitud = array_except($solicitud, ["imgOld"]);
      Multimedia::where('id', $id)->update($solicitud);
      Session::flash('snackbar-success', 'Archivo actualizado correctamente');
      return back();
    } else {
      $file_name =  Help::updateFile($input_name, $request);
      $file = $request->file($input_name);
      Eloquent::unguard();
      $archivo =  [
        'name' => $request->all()['name'],
        'url' => 'files/' . 'ac' . $file_name,
        'description' => $request->all()['description'],
        'type_file' => $file->getClientOriginalExtension(),
        'file_location' =>  env('APP_URL') . '/files/' . 'ac' . $file_name,
        'expiration' => $request['expiration'],
      ];
      $final = Multimedia::create($archivo);
      DB::table('fileables')->where('files_id', $id)->update([
        'files_id' => $final->id,
        'updated_at' => Carbon::now(),
      ]);
      Session::flash('snackbar-success', 'Archivo Actualizado correctamente');
      return redirect()->route('file.edit', $final->id);;
    }
  }

  public function findDestroy($id)
  {
    $input_name = 'all_files';
    $group = Multimedia::findOrFail($id)->toArray();
    $group = array_add($group, 'input_name', $input_name);
    return $group;
  }

  public function destroy($request)
  {
    $del = $this->findDestroy((int)$request);
    DB::table('fileables')->where('files_id', (int)$request)->delete();
    Help::deleteFile($del);
    Session::flash('snackbar-success', 'Archivo Eliminado correctamente');
    return back();
  }


  public function file_assign($request, $consult)
  {

    // separa la ejecución por $consult o por $request (si usa consult pase null en request)
    if ($request != null) {
      $archivo = $request->file;
      $id = $request->id;
      $type = $request->model;
    }
    if (is_array($consult)) {
      $archivo = $consult['file'];
      $id = $consult['id'];
      $type = $consult['model'];
    }
    // determina si los datos existen
    if (!isset($archivo) || !isset($id) || !isset($type)) {
      return 'parameter';
    }

    // valida el archivo si es existente
    $file = Multimedia::find($archivo);
    if ($file == null) {
      return 'parameter';
    }

    switch ($type) {
      case 'user':
        $model = User::find($id);
        break;
      case 'post':
        $model = Post::find($id);
        break;
      case 'slider':
        $model = Slider::find($id);
        break;
      case 'link':
        $model = Links::find($id);
        break;
      case 'options':
        $model = Options::find($id);
        break;
      case 'taxonomies':
        $model = Taxonomies::find($id);
        break;
      case 'laboratories':
        $model = Laboratories::find($id);
        break;
      case 'course':
        $model = Courses::find($id);
        break;
      case 'content':
        $model = Contents::find($id);
        break;
      case 'interaction':
        $model = Interaction::find($id);
        break;

      default:
        return 'parameter';
        break;
    }
    //determina si el modelo existe
    if ($model == null) {
      return 'parameter';
    }

    //valida si el archivo ya se encuentra registrado
    if (!$model->files->contains($archivo)) {
      //crea el elemento en la tabla polymorfica
      DB::table('fileables')->insert([
        'files_id' => $file->id,
        'fileable_id' => $model->id,
        'fileable_type' => get_class($model),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);
      return 'success';
    } else {
      return 'duplicate';
    }
  }

  // El contenido aqui descrito contiene los datos requeridos para ejecutar correctamente la consulta
  // fileable?file=18&id=1&model=options
  public function fileable(Request $request, $consult = null)
  {
    $msj = $this->file_assign($request, $consult);
    if ($msj == 'success') {
      Session::flash('snackbar-success', 'El Archivo se a asignado correctamente');
      return back();
    }
    if ($msj == 'parameter') {
      Session::flash('snackbar-danger', 'No se cuenta con todos los paramentros requeridos');
      return back();
    }
    if ($msj == 'duplicate') {
      Session::flash('snackbar-danger', 'No se puede cargar un archivo duplicado');
      return back();
    }
  }
}
