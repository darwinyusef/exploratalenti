<?php

namespace App\Http\Controllers;

use App\Entities\Contents;
use App\Entities\Taxonomies;
use App\Entities\Post;
use App\Entities\Courses;
use App\Entities\Interaction;
use Illuminate\Http\Request;
use Session, DB, Carbon\Carbon, Auth;

class TaxonomiesController extends Controller
{

  public function __construct()
  {
    $this->middleware(['permission:taxonomy.update'])->only(['update', 'edit']);
    $this->middleware(['permission:taxonomy.create'])->only(['create', 'store']);
    $this->middleware(['permission:taxonomy.list'])->only(['index']);
    $this->middleware(['permission:taxonomy.destroy'])->only(['destroy']);
    $this->middleware('auth');
  }

  public function valArray($type)
  {
    $array = ['all', 'category','item','tag','publicity', 'course', 'category-course','category-content','tag-content','tag-course'];
    
    if (in_array($type, $array)) {
      return 1;
    } else {
      return 0;
    }
  }


  public function index(Request $request)
  {
    if ($this->valArray($request->type) == 1) {
      $type = $request->type;
      if($request->type == 'all'){
        $taxonomies = Taxonomies::all();
      } else {
        $taxonomies = Taxonomies::where('type',  $type)->get(); 
      }
    } else {
      return abort(404);
    }

    
    return view('backend.taxonomy.list', compact('taxonomies'));
  }

  public function create()
  {
    $allTaxos = Taxonomies::all();
    return view('backend.taxonomy.create', compact('allTaxos'));
  }

  public function store(Request $request)
  {
    $make = $request->all();
    Taxonomies::create($make);
    Session::flash('snackbar-success', 'Taxonomia creado correctamente');
    return back();
  }

  public function edit($id)
  {
    $taxonomies = Taxonomies::findOrFail($id);
    $allTaxos = Taxonomies::all();
    return view('backend.taxonomy.update', compact('allTaxos', 'taxonomies'));
  }

  public function update(Request $request, $id)
  {
    $make = $request->all();
    Taxonomies::findOrFail($id)->update([
      'taxonomy' => $make['taxonomy'],
      'description' => $make['description'],
      'type' => $make['type'],
      'parent' => $make['parent']
    ]);
    Session::flash('snackbar-success', 'Taxonomia Actualizado correctamente');
    return back();
  }

  public function destroy($id, Request $request)
  {
    $force = $request->input('force');
    $options = Taxonomies::findOrFail($id);
    DB::table('taxonoables')->where('taxonomies_id', $id)->where('taxonoable_type', 'App\Entities\Post')->delete();
    DB::table('fileables')->where('fileable_id', (int)$id)->where('fileable_type', 'App\Entities\Taxonomies')->delete();
    if ($force == 1) {
      $options->forceDelete();
      Session::flash('snackbar-warning', 'La Taxonomia se ha Eliminado totalmente');
      return back();
    }
    $options->delete();
    Session::flash('snackbar-warning', 'La Taxonomia se ha envíado a la papelera');
    return back();
  }

  public function taxonoable(Request $request)
  {
    if ($this->valArray($request->type) == 1) {
      $type = $request->type;
      if( $request->type == 'all') {
        $taxo = Taxonomies::find($taxonomy);
      } else {
        $taxo = Taxonomies::where('type',  $type)->find($taxonomy);
      }
    } else {
      return 'Error en el tipo de documento';
    }
    // separa la ejecución por $consult o por $request (si usa consult pase null en request)
    if ($request != null) {
      $taxonomy = $request->taxonomy;
      $id =  $request->id;
      $model = Post::find($id);
      if ($request->model == 'course') {
        $model = Courses::find($id);
      }

      if ($request->model == 'content') {
        $model = Contents::find($id);
      }

      if ($request->model == 'interaction') {
        $model = Interaction::find($id);
      }

    }
    // determina si los datos existen
    if (!isset($taxonomy) || !isset($id) || !isset($type)) {
      return 'parameter';
    }
    

    //valida si el archivo ya se encuentra registrado
    if (!$model->taxonomy->contains($taxo)) {
      //crea el elemento en la tabla polymorfica
      DB::table('taxonoables')->insert([
        'taxonomies_id' => $taxo->id,
        'taxonoable_id' => $model->id,
        'taxonoable_type' => get_class($model),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);
      return 'ok';
    } else {
      DB::table('taxonoables')->where('taxonomies_id', $taxo->id)->where('taxonoable_id', $model->id)->delete();
      return 'Ha sido actualizado';
    }
  }
}
