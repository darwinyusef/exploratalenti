<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Redirect, Session;
use App\Http\Controllers\NotificationController as Notify; 

class CompanyController extends Controller
{
    public function index()
    {
        $company = Company::all();
        return Notify::ms('ok', 201, $company, 'Todos los archivos de compañia creados');
    }

    public function company_api()
    {
        $company = Company::all();
        return Notify::ms('ok', 201, $company, 'Todos los archivos de compañia creados');
    }

    public function create()
    {
        $company = Company::all();
        return Notify::ms('ok', 201, $company, 'Creados de compañia creados');
    }

    public function store(Request $request)
    {
        $make = $request->all();
        Company::create($make);
        return Notify::ms('ok', 201, $make, 'Se han guardado correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Links  $company
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return Notify::ms('ok', 201, $company, 'Lista de datos encontrados para edición');
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

        return Notify::ms('ok', 201, $make, 'La empresa Actualizado correctamente');
        // return Redirect::to('/backend/empresa');
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
            return Notify::ms('ok', 201, [], 'La empresa se ha Eliminado totalmente');
            // return Redirect::to('/backend/empresa');
        }
        $options->delete();
        Session::flash(
            'snackbar-warning',
            'La empresa se ha envíado a la papelera'
        );
        return Notify::ms('ok', 201, [], 'La empresa se ha envíado a la papelera');
        // return Redirect::to('/backend/empresa');
    }
}
