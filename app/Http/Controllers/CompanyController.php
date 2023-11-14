<?php

namespace App\Http\Controllers;

use App\Entities\Company;
use Illuminate\Http\Request;
use Redirect, Session;

class CompanyController extends Controller
{
    public function index()
    {
        $company = Company::all();
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Todos los archivos de compañia creados',
                'error' => $company,
            ],
            201
        );
    }

    public function company_api()
    {
        $company = Company::all();
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Todos los archivos de compañia creados',
                'error' => $company,
            ],
            201
        );
    }

    public function create()
    {
        $company = Company::all();
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Creados de compañia creados',
                'error' => $company,
            ],
            201
        );
    }

    public function store(Request $request)
    {
        $make = $request->all();
        Company::create($make);
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Se han guardado correctamente',
                'error' => $make,
            ],
            201
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\Links  $company
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Lista de datos encontrados para edición',
                'error' => $company,
            ],
            201
        );
    }

    public function update(Request $request, $id)
    {
        $make = $request->all();
        Company::findOrFail($id)->update([
            'company' => $make['company'],
            'nit' => $make['nit'],
            //'parent' => $make['parent'],
        ]);
        Session::flash(
            'snackbar-success',
            'La empresa Actualizado correctamente'
        );
        return Redirect::to('/backend/empresa');
    }

    public function destroy($id, Request $request)
    {
        $force = $request->input('force');
        $options = Company::findOrFail($id);
        if ($force == 1) {
            $options->forceDelete();
            Session::flash(
                'snackbar-warning',
                'La empresa se ha Eliminado totalmente'
            );
            return Redirect::to('/backend/empresa');
        }
        $options->delete();
        Session::flash(
            'snackbar-warning',
            'La empresa se ha envíado a la papelera'
        );
        return Redirect::to('/backend/empresa');
    }
}
