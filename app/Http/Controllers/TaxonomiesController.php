<?php

namespace App\Http\Controllers;

use App\Models\Contents;
use App\Models\Taxonomies;
use App\Models\Post;
use App\Models\Courses;
use App\Models\Interaction;
use Illuminate\Http\Request;
use Session, DB, Carbon\Carbon, Auth;
use App\Http\Controllers\NotificationController as Notify; 

class TaxonomiesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:taxonomy.update'])->only([
            'update',
            'edit',
        ]);
        $this->middleware(['permission:taxonomy.create'])->only([
            'create',
            'store',
        ]);
        $this->middleware(['permission:taxonomy.list'])->only(['index']);
        $this->middleware(['permission:taxonomy.destroy'])->only(['destroy']);
        
    }

    public function valArray($type)
    {
        $array = [
            'all',
            'category',
            'item',
            'tag',
            'publicity',
            'course',
            'category-course',
            'category-content',
            'tag-content',
            'tag-course',
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
            if ($request->type == 'all') {
                $taxonomies = Taxonomies::all();
            } else {
                $taxonomies = Taxonomies::where('type', $type)->get();
            }
        } else {
            return Notify::ms('no-found', 404, $validator, 'No existe la taxonomia');
        }
        // view: backend.taxonomy.list
        return Notify::ms('ok', 201, $taxonomies, 'Se a listado correctamente');
    }

    public function create()
    {
        $allTaxos = Taxonomies::all();
        // view: backend.taxonomy.create
        return Notify::ms('ok', 201, $allTaxos, 'Se a listado correctamente');
    }

    public function store(Request $request)
    {
        $make = $request->all();
        Taxonomies::create($make);
        Session::flash('snackbar-success', 'Taxonomia creado correctamente');
        return Notify::ms('ok', 201, [], 'Taxonomia creado correctamente');
    }

    public function edit($id)
    {
        $taxonomies = Taxonomies::findOrFail($id);
        $allTaxos = Taxonomies::all();
        // view: backend.taxonomy.update
        return Notify::ms(
            'ok',
            201,
            [$allTaxos, $taxonomies],
            'Se a listado correctamente'
        );
    }

    public function update(Request $request, $id)
    {
        $make = $request->all();
        Taxonomies::findOrFail($id)->update([
            'taxonomy' => $make['taxonomy'],
            'description' => $make['description'],
            'type' => $make['type'],
            'parent' => $make['parent'],
        ]);
        Session::flash(
            'snackbar-success',
            'Taxonomia Actualizado correctamente'
        );
        return Notify::ms('ok', 201, [], 'Taxonomia Actualizado correctamente');
    }

    public function destroy($id, Request $request)
    {
        $force = $request->input('force');
        $options = Taxonomies::findOrFail($id);
        DB::table('taxonoables')
            ->where('taxonomies_id', $id)
            ->where('taxonoable_type', 'App\Models\Post')
            ->delete();
        DB::table('fileables')
            ->where('fileable_id', (int) $id)
            ->where('fileable_type', 'App\Models\Taxonomies')
            ->delete();
        if ($force == 1) {
            $options->forceDelete();
            Session::flash(
                'snackbar-warning',
                'La Taxonomia se ha Eliminado totalmente'
            );
            return Notify::ms(
                'ok',
                201,
                [],
                'La Taxonomia se ha Eliminado totalmente'
            );
        }
        $options->delete();
        Session::flash(
            'snackbar-warning',
            'La Taxonomia se ha envíado a la papelera'
        );
        return Notify::ms(
            'ok',
            201,
            [],
            'La Taxonomia se ha envíado a la papelera'
        );
    }

    public function taxonoable(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $type = $request->type;
            if ($request->type == 'all') {
                $taxo = Taxonomies::find($taxonomy);
            } else {
                $taxo = Taxonomies::where('type', $type)->find($taxonomy);
            }
        } else {
            return Notify::ms('ok', 201, [], 'Error en el tipo de documento');
        }
        // separa la ejecución por $consult o por $request (si usa consult pase null en request)
        if ($request != null) {
            $taxonomy = $request->taxonomy;
            $id = $request->id;
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
            return Notify::ms('ok', 201, [], 'parameter');
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
            return Notify::ms('ok', 201, [], 'Se a listado correctamente');
        } else {
            DB::table('taxonoables')
                ->where('taxonomies_id', $taxo->id)
                ->where('taxonoable_id', $model->id)
                ->delete();
            return Notify::ms('ok', 201, [], 'Ha sido actualizado');
        }
    }
}
