<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\Services;
use App\Http\Controllers\NotificationController as Notify; 

use Validator, Redirect;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Categories::all();
        return Notify::ms('ok', 201, $final, 'Se a listado correctamente');
    }

    public function create()
    {
        $services = Services::all();
        return Notify::ms('ok', 201, $services, 'Se a listado correctamente');
    }

    public function store(Request $request)
    {
        //Se transforma la socitud a array y se limpia para enviar
        $data = $request->toArray();
        $data = array_add($data, 'slug', str_slug($data['name'], '-'));
        $validator = Validator::make($data, Categories::$rules);
        if ($validator->fails()) {
            return Notify::ms('error', 400, $validator, 'Validations Error');
        } else {
            // se carga la solicitud
            $final = Categories::create($data);
            return Notify::ms(
                'ok',
                201,
                $final,
                'Se a creado un nuevo usuario'
            );
        }
    }

    public function edit($id)
    {
        $services = Services::all();
        $category = Categories::findOrFail($id);
        return Notify::ms(
            'ok',
            201,
            [$category, $services],
            'Se a listado correctamente'
        );
    }

    public function update(Request $request, $id)
    {
        $data = $request->toArray();
        $data = array_add($data, 'slug', str_slug($data['name'], '-'));
        $data = array_except($data, ['_method']);
        $data = array_except($data, ['_token']);
        //se modifican las entradas on x 1 para que se valide el bolean
        $validator = Validator::make($data, Categories::$rules);
        if ($validator->fails()) {
            return Notify::ms('error', 400, $validator, 'Validations Error');
        } else {
            // se carga la solicitud
            $final = Categories::where('id', $id)->update($data);
            return Notify::ms('ok', 201, $final, 'Se a Editado el Registro');
        }
    }

    public function destroy(Request $request, $id)
    {
        $force = $request->input('force');
        $group = Categories::findOrFail($id);

        if ($force == 1) {
            $group->forceDelete();
            return Notify::ms('ok', 201, $group, 'Se a Elimninado el Registro');
        }
        $group->delete();
        return Notify::ms('ok', 201, $group, 'Se a Elimninado el Registro');
    }
}
