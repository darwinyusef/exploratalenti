<?php

namespace App\Http\Controllers;

use App\Http\Controllers\NotificationController as Notify; 
use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Taxonomies;
use Auth, Session;


class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:post.update'])->only(['update', 'edit']);
        $this->middleware(['permission:post.create'])->only([
            'create',
            'store',
        ]);
        $this->middleware(['permission:post.list'])->only(['index']);
        $this->middleware(['permission:post.destroy'])->only(['destroy']);
        
    }

    public function valArray($type)
    {
        $array = [
            'attachment',
            'page',
            'post',
            'revision',
            'portfolio',
            'directory',
            'publicity',
            'course',
            'homework',
            'reading',
            'leader',
        ];

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
        // view: backend.post.list
        return Notify::ms(
            'ok',
            201,
            [$type, $posts],
            'Se a listado correctamente'
        );
    }

    public function create(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $type = $request->type;
        }
        // view: backend.post.create
        return Notify::ms('ok', 201, $type, 'Se a listado correctamente');
    }

    public function store(Request $request)
    {
        $make = $request->all();
        if ($make['state'] == 'draft') {
            $make['password'] = $make['password'];
        } else {
            $make = array_except($make, 'password');
        }
        $make = array_except($make, '_token');
        $make = array_add($make, 'user_id', Auth::user()->id);

        Post::create($make);
        Session::flash('snackbar-success', 'Post creado correctamente');
        return Notify::ms('ok', 201, [], 'Se a listado correctamente');
    }

    public function add_img()
    {
        $post = Post::find(1)
            ->files()
            ->create(['files_id' => 1]);
        Session::flash('snackbar-success', 'Post creado correctamente');
        return Notify::ms('ok', 201, $post, 'Se a listado correctamente');
    }

    public function edit($id, Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $type = $request->type;
        }
        $taxonomies = Taxonomies::all();
        $posts = Post::where('id', $id)
            ->where('type', $type)
            ->first();
        // view: backend.post.update
        return Notify::ms(
            'ok',
            201,
            [$type, $posts, $taxonomies],
            'Se a listado correctamente'
        );
    }

    public function update(Request $request, $id)
    {
        $make = $request->all();
        if ($make['type_list'] == 'draft') {
            $make['password'] = $make['password'];
        } else {
            $make['password'] = null;
        }
        $make = array_except($make, '_method');
        $make = array_except($make, '_token');
        $make = array_except($make, 'type_list');
        $make = array_add($make, 'user_id', Auth::user()->id);

        Post::where('id', $id)->update($make);
        Session::flash('snackbar-success', 'Post editado correctamente');
        return Notify::ms('ok', 201, [], 'Post editado correctamente');
    }

    public function destroy(Request $request, $id)
    {
        $force = $request->input('force');
        $options = Post::findOrFail($id);
        if ($force == 1) {
            $options->forceDelete();
            Session::flash(
                'snackbar-warning',
                'El Post se ha Eliminado totalmente'
            );
            return Notify::ms('ok', 201, [], 'El Post se ha Eliminado totalmente');
        }
        $options->delete();
        Session::flash(
            'snackbar-warning',
            'El Post se ha envíado a la papelera'
        );
        return Notify::ms('ok', 201, [], 'El Post se ha envíado a la papelera');
    }
}
