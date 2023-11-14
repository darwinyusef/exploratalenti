<?php

namespace App\Http\Controllers;

use App\Entities\Links;
use Illuminate\Http\Request;
use Session, Redirect;

class LinksController extends Controller{
  public function __construct(){
    $this->middleware(['permission:link.update'])->only(['update', 'edit']);
    $this->middleware(['permission:link.create'])->only(['create', 'store']);
    $this->middleware(['permission:link.list'])->only(['index']);
    $this->middleware(['permission:link.destroy'])->only(['destroy']);
    $this->middleware('auth');
  }

    public function index() {
        $links = Links::all();

        return view('backend.links.list', compact('links'));
    }


    public function create(){
      $allLinks = Links::all();
      return view('backend.links.create', compact('allLinks'));
    }

    public function store(Request $request) {
      $make = $request->all();
      Links::create($make);
      Session::flash('snackbar-success', 'Link creado correctamente');
      return Redirect::to('/backend/menu');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\Links  $links
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        $links = Links::findOrFail($id);
        $allLinks = Links::all();
        return view('backend.links.update', compact('links', 'allLinks'));
    }

    public function update(Request $request, $id)    {
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
      return Redirect::to('/backend/menu');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\Links  $links
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request) {
      $force = $request->input('force');
        $options = Links::findOrFail($id);
        if($force == 1){
          $options->forceDelete();
            Session::flash('snackbar-warning', 'El usuario se ha Eliminado totalmente');
            return Redirect::to('/backend/menu');
        }
        $options->delete();
        Session::flash('snackbar-warning', 'El usuario se ha envíado a la papelera');
        return Redirect::to('/backend/menu');
    }
}
