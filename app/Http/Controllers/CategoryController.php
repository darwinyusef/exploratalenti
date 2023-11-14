<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Categories;
use App\Entities\Services;

use Validator, Redirect;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Categories::all();
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Student Update Successfully',
                'data' => $categories,
            ],
            201
        );
    }

    public function create()
    {
        $services = Services::all();
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Student Update Successfully',
                'data' => $services,
            ],
            201
        );
    }

    public function store(Request $request)
    {
        //Se transforma la socitud a array y se limpia para enviar
        $data = $request->toArray();
        $data = array_add($data, 'slug', str_slug($data['name'], '-'));
        $validator = Validator::make($data, Categories::$rules);
        if ($validator->fails()) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' => 'Validations Error',
                    'error' => $validator,
                ],
                400
            );
        } else {
            // se carga la solicitud
            Categories::create($data);

            return response()->json(
                [
                    'type' => 'ok',
                    'message' => 'Se a creado un nuevo usuario',
                ],
                201
            );
        }
    }

    public function edit($id)
    {
        $services = Services::all();
        $category = Categories::findOrFail($id);
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Se a creado un nuevo usuario',
                'data' => [$category, $services],
            ],
            201
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
            return response()->json(
                [
                    'type' => 'error',
                    'message' => 'Validations Error',
                    'error' => $validator,
                ],
                400
            );
        } else {
            // se carga la solicitud
            Categories::where('id', $id)->update($data);
            return response()->json(
                [
                    'type' => 'ok',
                    'message' => 'Se a Editado el Registro',
                    'data' => [$category, $services],
                ],
                201
            );
        }
    }

    public function destroy(Request $request, $id)
    {
        $force = $request->input('force');
        $group = Categories::findOrFail($id);

        if ($force == 1) {
            $group->forceDelete();
            return response()->json(
                [
                    'type' => 'ok',
                    'message' => 'Se a Elimninado el Registro',
                    'data' => [$group],
                ],
                201
            );
        }
        $group->delete();
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Se a Elimninado el Registro',
                'data' => [$group],
            ],
            201
        );
    }
}
