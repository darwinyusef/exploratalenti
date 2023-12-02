<?php

namespace App\Http\Controllers;

use App\Models\Links;
use Illuminate\Http\Request;
use Session, Redirect;
use App\Http\Controllers\NotificationController as Notify; 

class LinksController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:link.update'])->only(['update', 'edit']);
        $this->middleware(['permission:link.create'])->only([
            'create',
            'store',
        ]);
        $this->middleware(['permission:link.list'])->only(['index']);
        $this->middleware(['permission:link.destroy'])->only(['destroy']);
        
    }

    public function index()
    {
        $links = Links::all();
        // view: backend.links.list
        return Notify::ms('ok', 201, $links, 'Se a listado correctamente');
    }

    public function create()
    {
        $allLinks = Links::all();
        return Notify::ms('ok', 201, $allLinks, 'Se a listado correctamente');
    }

    public function store(Request $request)
    {
        $make = $request->all();
        Links::create($make);
        Session::flash('snackbar-success', 'Link creado correctamente');
        // return Redirect::to('/backend/menu');
        return Notify::ms('ok', 201, $make, 'Link creado correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Links  $links
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $links = Links::findOrFail($id);
        $allLinks = Links::all();
        // view: backend.links.update
        return Notify::ms(
            'ok',
            201,
            [$links, $allLinks],
            'Se a listado correctamente'
        );
    }

    public function update(Request $request, $id)
    {
        $make = $request->all();
        Links::findOrFail($id)->update([
            'url' => $make['url'],
            'name' => $make['name'],
            'icon' => $make['icon'],
            'target' => $make['target'],
            'description' => $make['description'],
            'visible' => $make['visible'],
            'location' => $make['location'],
            'notes' => $make['notes'],
            'parent_id' => $make['parent_id'],
        ]);

        Session::flash('snackbar-success', 'Link Actualizado correctamente');
        return Notify::ms('ok', 201, [], 'Link Actualizado correctamente');
        // return Redirect::to('/backend/menu');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Links  $links
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $force = $request->input('force');
        $options = Links::findOrFail($id);
        if ($force == 1) {
            $options->forceDelete();
            Session::flash(
                'snackbar-warning',
                'El mensaje se ha Eliminado totalmente'
            );
            return Notify::ms('ok', 201, [], 'El mensaje se ha Eliminado totalmente');
        }
        $options->delete();
        Session::flash(
            'snackbar-warning',
            'El mensaje se ha envíado a la papelera'
        );
        return Notify::ms('ok', 201, [], 'El mensaje se ha Eliminado totalmente');
    }
}
