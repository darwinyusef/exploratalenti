<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\Request;
use App\Entitnes\Comunications;
use App\Notifications\MessageUser;
use App\Models\User;
use App\Http\Controllers\NotificationController as Notify; 
use DB, Validator, Session, Redirect, Auth;

class ComunicationsController extends Controller
{
    public function index()
    {
        return Redirect::to('mensaje/create');
    }
    public function create()
    {
        $roles = DB::table('roles')->get();
        $menssages = Comunications::paginate(20);
        $users = User::where('id', '<>', Auth::user()->id)->get();
        // view: backend.comunication.create
        return Notify::ms(
            'ok',
            201,
            [$roles, $menssages, $users],
            'Se a listado correctamente'
        );
    }

    public function store(Request $request)
    {
        //Se transforma la socitud a array y se limpia para enviar
        $data = $request->toArray();
        $data = array_except($data, ['_token']);
        $validator = Validator::make($data, Comunications::$rules);
        if ($validator->fails()) {
            return Notify::ms('error', 400, $validator, 'Validations Error');
        } else {
            // se carga la solicitud
            $message = Comunications::create($data);

            if ($data['location'] == 'user') {
                $user = User::find($data['users_id']);
                $user->notify(new MessageUser($message));
            }

            Session::flash('sw-mensaje', '');
            return Notify::ms('ok', 201, [], 'Se a creado un nuevo Mensaje');
            // return Redirect::to('mensaje/create');
        }
    }

    public function edit($id)
    {
        $menssages = Comunications::findOrFail($id);
        $roles = DB::table('roles')->get();
        // view: backend.comunication.edit
        return Notify::ms(
            'ok',
            201,
            [$roles, $menssages],
            'Se a listado correctamente'
        );
    }

    public function update(Request $request, $id)
    {
        $data = $request->toArray();
        $data = array_except($data, ['_method']);
        $data = array_except($data, ['_token']);

        //se modifican las entradas on x 1 para que se valide el bolean
        $validator = Validator::make($data, Comunications::$rules);
        if ($validator->fails()) {
            return Notify::ms('error', 400, $validator, 'Validations Error');
        } else {
            // se carga la solicitud
            Comunications::where('id', $id)->update($data);
            Session::flash('snackbar-success', 'Se a Editado el Registro');
            return Notify::ms('ok', 201, $final, 'Se a Editado el Registro');
            // return Redirect::to('mensaje/');
        }
    }

    public function addmenuTuto(Request $request)
    {
        return Comunications::where('location', $request->location)
            ->where('rol', $request->rol)
            ->get();
    }

    public function read($id)
    {
        DatabaseNotification::find($id)->markAsRead();
        Session::flash('snackbar-success', 'Notificación Leida');
        return back();
    }

    public function destroy(Comunications $menssaje)
    {
    }
}
