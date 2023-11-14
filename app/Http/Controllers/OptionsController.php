<?php

namespace App\Http\Controllers;

use App\Entities\Options;
use Illuminate\Http\Request;
use Carbon\Carbon, Session;

class OptionsController extends Controller {

    public function __construct(){
      $this->middleware(['permission:option.update'])->only(['update', 'edit']);
      $this->middleware(['permission:option.create'])->only(['create', 'store']);
      $this->middleware(['permission:option.list'])->only(['index']);
      $this->middleware(['permission:option.destroy'])->only(['destroy']);
      $this->middleware('auth');
    }

    public function index() {
      $options = Options::all();
      return view('backend.options.list', compact('options'));
    }

    public function create() {
        return view('backend.options.create');
    }


    public function store(Request $request) {
      $make = $request->all();
      $make['time_in'] = Carbon::parse($make['time_in']);
      $make['time_out'] = Carbon::parse($make['time_out']);
      Options::create($make);
      Session::flash('snackbar-success', 'Post creado correctamente');
      return back();
    }

    public function edit($id) {
        $option = Options::findOrFail($id);
        return view('backend.options.update', compact('option'));
    }

    public function update(Request $request, $id) {
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
            'status' => $make['status']
          ]);
          Session::flash('snackbar-success', 'Post Actualizado correctamente');
          return back();
    }

    public function destroy($id, Request $request) {
      $force = $request->input('force');
        $options = Options::findOrFail($id);
        if($force == 1){
          $options->forceDelete();
            Session::flash('snackbar-warning', 'El usuario se ha Eliminado totalmente');
            return back();
        }
        $options->delete();
        Session::flash('snackbar-warning', 'El usuario se ha envíado a la papelera');
        return back();
    }
}
