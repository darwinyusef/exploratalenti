<?php

namespace App\Http\Controllers;

use App\Entities\Post;
use Illuminate\Http\Request;
use App\Entities\Taxonomies;
use Auth, Session;

class PostController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:post.update'])->only(['update', 'edit']);
    $this->middleware(['permission:post.create'])->only(['create', 'store']);
    $this->middleware(['permission:post.list'])->only(['index']);
    $this->middleware(['permission:post.destroy'])->only(['destroy']);
    $this->middleware('auth');
  }

  public function valArray($type)
  {
    $array = ['attachment', 'page', 'post', 'revision', 'portfolio', 'directory', 'publicity', 'course','homework','reading','leader'];
    
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
    }
    $posts = Post::where('type', $type)->get();
    return view('backend.post.list', compact('type', 'posts'));
  }

  public function create(Request $request){
    if( $this->valArray($request->type) == 1 ){
      $type = $request->type;
    }
      return view('backend.post.create', compact('type'));
  }


  public function store(Request $request)
  {
    $make = $request->all();
    if ($make['state'] == 'draft') {
      $make['password'] = $make['password'];
    } else {
      $make =  array_except($make, 'password');
    }
    $make = array_except($make, '_token');
    $make = array_add($make, 'user_id', Auth::user()->id);

    Post::create($make);
    Session::flash('snackbar-success', 'Post creado correctamente');
    return back();
  }

  public function add_img()
  {
    $post = Post::find(1)->files()->create(['files_id' => 1]);
    Session::flash('snackbar-success', 'Post creado correctamente');
    return $post;
  }


  public function edit($id, Request $request)
  {
    if ($this->valArray($request->type) == 1) {
      $type = $request->type;
    }
    $taxonomies = Taxonomies::all();
    $posts = Post::where('id', $id)->where('type', $type)->first();
    return view('backend.post.update', compact('type', 'posts', 'taxonomies'));
  }

  public function update(Request $request, $id ) {
    $make = $request->all();
    if($make['type_list'] == 'draft'){
        $make['password'] = $make['password'];
    }else{
       $make['password'] = null;
    }
    $make = array_except($make, '_method');
    $make = array_except($make, '_token');
    $make = array_except($make, 'type_list');
    $make = array_add($make, 'user_id', Auth::user()->id);

    Post::where('id', $id)->update($make);
    Session::flash('snackbar-success', 'Post editado correctamente');
    return back();
  }

  public function destroy(Request $request, $id)
  {
    $force = $request->input('force');
    $options = Post::findOrFail($id);
    if ($force == 1) {
      $options->forceDelete();
      Session::flash('snackbar-warning', 'El Post se ha Eliminado totalmente');
      return back();
    }
    $options->delete();
    Session::flash('snackbar-warning', 'El Post se ha envíado a la papelera');
    return back();
  }
  
}
