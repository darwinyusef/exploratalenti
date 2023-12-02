<?php

namespace App\Http\Controllers;

use App\Models\Options;
use Illuminate\Http\Request;
use Carbon\Carbon, Session;
use App\Http\Controllers\NotificationController as Notify; 

class OptionsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:option.update'])->only([
            'update',
            'edit',
        ]);
        $this->middleware(['permission:option.create'])->only([
            'create',
            'store',
        ]);
        $this->middleware(['permission:option.list'])->only(['index']);
        $this->middleware(['permission:option.destroy'])->only(['destroy']);
        
    }

    public function index()
    {
        $options = Options::all();
        // view: backend.options.list
        return Notify::ms('ok', 201, $options, 'Se a listado correctamente');
    }

    public function create()
    {
        // view: backend.options.create
        return Notify::ms('ok', 201, [], 'Se a listado correctamente');
    }

    public function store(Request $request)
    {
        $make = $request->all();
        $make['time_in'] = Carbon::parse($make['time_in']);
        $make['time_out'] = Carbon::parse($make['time_out']);
        Options::create($make);
        Session::flash('snackbar-success', 'Post creado correctamente');
        return Notify::ms('ok', 201, [], 'Post creado correctamente');
    }

    public function edit($id)
    {
        $option = Options::findOrFail($id);
        // view: backend.options.update
        return Notify::ms('ok', 201, $option, 'Se a listado correctamente');
    }

    public function update(Request $request, $id)
    {
        $make = $request->all();

        $make['time_in'] = Carbon::parse($make['time_in']);
        $make['time_out'] = Carbon::parse($make['time_out']);
        Options::findOrFail($id)->update([
            'option_key' => $make['option_key'],
            'option_value' => $make['option_value'],
            'settings' => $make['settings'],
            'autoload' => $make['autoload'],
            'time_in' => $make['time_in'],
            'time_out' => $make['time_out'],
            'status' => $make['status'],
        ]);
        Session::flash('snackbar-success', 'Post Actualizado correctamente');
        return Notify::ms('ok', 201, [], 'Se a listado correctamente');
    }

    public function destroy($id, Request $request)
    {
        $force = $request->input('force');
        $options = Options::findOrFail($id);
        if ($force == 1) {
            $options->forceDelete();
            Session::flash(
                'snackbar-warning',
                'El post se ha Eliminado totalmente'
            );
            return Notify::ms('ok', 201, [], 'El post se ha Eliminado totalmente');
        }
        $options->delete();
        Session::flash(
            'snackbar-warning',
            'El post se ha envíado a la papelera'
        );
        return Notify::ms('ok', 201, [], 'El post se ha envíado a la papelera');
    }
}
