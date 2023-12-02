<?php

namespace App\Http\Controllers;

use App\Models\DataUser as Data;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\helperController as Helps;
use App\Http\Controllers\NotificationController as Notify; 

use Validator, Session, Redirect, Auth, Carbon\Carbon, Mail, DB, Hash;

class UserAllController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:user.list'])->only(['index']);
        $this->middleware(['permission:user.create'])->only([
            'create',
            'store',
        ]);
        $this->middleware(['permission:user.update'])->only(['update', 'edit']);
        $this->middleware(['permission:user.destroy'])->only(['destroy']);
        
    }

    public function changeTheme(Request $request)
    {
        User::find(Auth::user()->id)->update([
            'theme' => $request->theme,
        ]);
        return ['msj' => 'ok'];
    }

    public function index(Request $request)
    {
        $total = $request->total;
        $email = $request->email;
        $cedula = $request->cedula;

        if ($total) {
            $total = $total;
        } else {
            $total = 30;
        }

        $users = User::leftJoin('data_users', 'user_id', '=', 'users.id')
            ->leftJoin(
                'companies',
                'data_users.company_id',
                '=',
                'companies.id'
            )
            ->orderBy('users.id', 'desc')
            ->select(
                'first',
                'last',
                'email',
                'company',
                'users.id as id',
                'data_users.company_id',
                'data_users.id as id_data',
                'data_users.mobile',
                'data_users.address',
                'type_card',
                'card_id',
                'data_users.neighborhood'
            );

        if ($email) {
            $users = $users->where('email', 'LIKE', '%' . $email . '%');
        } else {
            $users = $users;
        }

        if ($cedula) {
            $users = $users->where('card_id', $cedula);
        } else {
            $users = $users;
        }

        if (Auth::user()->hasPermissionTo('user.list.all')) {
            $users = $users->paginate($total);
        } else {
            abort(403);
        }
        // view: backend.user.user-list
        return Notify::ms('ok', 201, $users, 'Se a listado correctamente');
    }

    public function create()
    {
        $company = Company::all();
        return Notify::ms('ok', 201, $company, 'Se a listado correctamente');
        // view: backend.user.user-create
    }

    public function store(Request $request)
    {
        $solicitud = $request->all();
        $password = $solicitud['password'];
        $validator = Validator::make($solicitud, User::$rules);
        if ($validator->fails()) {
            Session::flash(
                'snackbar-info',
                'Problema al carcargar la información:'
            );
            return redirect('/backend/usuarios/create')
                ->withErrors($validator)
                ->withInput();
        } else {
            $name = $solicitud['first'] . ' ' . $solicitud['last'];
            $slug = str_slug($name, '-');
            $solicitud = array_add($solicitud, 'name', $name);
            $solicitud = array_add($solicitud, 'status', 1);
            $solicitud = array_add($solicitud, 'slug', $slug);
            $solicitud = array_add($solicitud, 'active', 0);
            $solicitud = array_add(
                $solicitud,
                'validate_token',
                str_random(40)
            );
            $solicitud['password'] = bcrypt($password);
            $user = User::create($solicitud);
            // se carga la solicitud

            $solicitud = array_add($solicitud, 'user_id', $user->id);
            Data::create($solicitud);
            $user->assignRole('estudiante');

            $data = [
                'name' => $solicitud['first'] . ' ' . $solicitud['last'],
                'email' => $solicitud['email'],
                'password' => $password,
                'user' => $user->id,
                'remember_token' => $user->validate_token,
                'url' =>
                    env('APP_URL') .
                    '/verifyemail?type=&token=' .
                    $user->validate_token .
                    '&email=' .
                    $solicitud['email'] .
                    '&live=' .
                    $user->id,
            ];

            Mail::send('backend.mails.registerEmailNew', $data, function (
                $message
            ) use ($data) {
                $message->subject('Verifique su Email - Notification');
                $message->from('no-reply@aquicreamos.com');
                $message->to($data['email']);
            });

            Session::flash(
                'snackbar-success',
                'Usuario Cargado Correctamente: ' . $user->email
            );
            return Notify::ms(
                'ok',
                201,
                $company,
                'Se a creado el usuario correctamente'
            );
        }
    }

    public function edit($id)
    {
        if (Auth::user()->hasRole('admin')) {
            $id = $id;
        } elseif (Auth::user()->hasPermissionTo('user.my')) {
            $id = Auth::user()->id;
        }
        $user = Data::where('user_id', $id)->first();
        $profile = User::find($user->user_id);
        $company = Company::all();
        // view: backend.user.profile-update
        return Notify::ms(
            'ok',
            201,
            [$user, $profile, $company],
            'Se a listado correctamente'
        );
    }

    public function show($id)
    {
        if (Auth::user()->hasRole('admin')) {
            $id = $id;
            $profile = User::find($id);
        } elseif (Auth::user()->hasPermissionTo('user.my')) {
            $id = Auth::user()->id;
            $profile = Auth::user();
        }
        $user = Data::where('user_id', $id)->first();
        $company = Company::where('id', $user->company_id)->first();
        // view: backend.user.profile-show
        return Notify::ms(
            'ok',
            201,
            [$user, $profile, $company],
            'Se a listado correctamente'
        );
    }

    public function update($id, Request $request)
    {
        $solicitud = $request->all();
        $validator = Validator::make($solicitud, User::$rulesUpdate);
        if ($validator->fails()) {
            Session::flash(
                'snackbar-info',
                'Problema al carcargar la información:'
            );
            return redirect('/backend/usuarios/' . $id . '/edit')
                ->withErrors($validator)
                ->withInput();
        } else {
            $institution =
                $request->company_id .
                ';' .
                Company::where('id', $request->company_id)->first()->company;
            // modificar caracteres especiales ?= htmlentities(Helps::del_symbols($string2));
            $solicitud = [
                'id' => $request->moodle,
                'email' => $request->email,
                'first' => htmlentities(Helps::del_symbols($request->first)),
                'last' => htmlentities(Helps::del_symbols($request->last)),
                'phone1' => $request->mobile,
                'phone2' => $request->phone_home,
                'institution' => $institution,
                'address' => $request->address,
                // "password" => '$2y$10$nYIqAaqnjI4LNJBGCg1qj.jXOC9gq9pr6rG9uN1oHc.93RIQZbJqa',
            ];

            $url =
                'https://diagnosticar.com.co/moodle/register/get_update_laravel.php';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $solicitud);
            // execute!
            $response = curl_exec($ch);
            // close the connection, release resources used
            curl_close($ch);
            // do anything you want with your response
            $rest = json_decode($response);

            if ($rest->response == 'ok') {
                $solicitud = $request->all();
                $name = $solicitud['first'] . ' ' . $solicitud['last'];
                $slug = str_slug($name, '-');
                $solicitud = array_add($solicitud, 'name', $name);
                $solicitud = array_add($solicitud, 'slug', $slug);
                /*if($solicitud['active'] == 1){
              $solicitud = array_add($solicitud, 'active', $solicitud['active']);
              $solicitud = array_add($solicitud, 'pago', $solicitud['active']);
              $solicitud = array_add($solicitud, 'status', $solicitud['active']);
              $solicitud = array_add($solicitud, 'validate_token', null);
          }*/
                $users = array_only($solicitud, [
                    'email',
                    'name',
                    'active',
                    'slug',
                    'pago',
                    'status',
                    'validate_token',
                ]);
                if (Auth::user()->hasRole('admin')) {
                    $id = $id;
                } elseif (Auth::user()->hasPermissionTo('user.my')) {
                    $id = Auth::user()->id;
                }

                User::where('id', $id)->update($users);
                $user = User::where('id', $id)
                    ->select('id', 'email')
                    ->first();
                /*$birth = Carbon::parse( str_replace ("/", "-",   $solicitud['birth'] ) )->format('Y-m-d');
                 $solicitud['birth'] = $birth;*/
                $datas = array_only($solicitud, [
                    'first',
                    'last',
                    'type_card',
                    'card_id',
                    'postcode',
                    'gender',
                    'mobile',
                    'phone_home',
                    'address',
                    'neighborhood',
                    'company_id',
                ]);
                $data = Data::where('user_id', $user->id)->update($datas);
                Session::flash(
                    'snackbar-success',
                    'Usuario Actualizado Correctamente: ' . $user->email
                );
                return redirect('/backend/usuarios/' . $id . '/edit');
            }
        }

        $validator = Validator::make($request->all(), User::$rules);
        if ($validator->fails()) {
            return redirect('/backend/usuarios/' . $id . '/edit')
                ->withErrors($validator)
                ->withInput();
        } else {
            User::where('id', $id)->update($request->all());
            return redirect('/backend/usuarios/' . $id);
        }
    }

    public function destroy($id, Request $request)
    {
        $force = $request->input('force');
        $group = Data::findOrFail($id);
        $user = User::findOrFail($group->user_id);
        $group->delete();
        $user->delete();
        if ($force == 1) {
            $group->forceDelete();
            $user->forceDelete();
            return 1;
        }
        Session::flash(
            'snackbar-warning',
            'El usuario se ha movido a la papelera de reciclaje'
        );
        return redirect('/backend/usuarios');
    }

    public function pass(Request $request)
    {
        $id = Auth::user()->id;
        if (
            Auth::user()->hasRole('admin') ||
            Auth::user()->hasRole('docente')
        ) {
            if ($request->id != null) {
                $id = $request->id;
            }
        }
        // view: backend.user.profile-password-change
        return Notify::ms('ok', 201, $id, 'Se a listado correctamente');
    }

    public function pass_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            Session::flash('snackbar-danger', 'Error');
            return back()
                ->withErrors($validator)
                ->withInput();
        } elseif (Hash::check($request->old_password, Auth::user()->password)) {
            if (
                Auth::user()->hasRole('admin') ||
                Auth::user()->hasRole('docente')
            ) {
                $finalId = $request->id;
                $user = User::find($request->id);
            } else {
                $finalId = Auth::user()->id;
                $user = Auth::user();
            }

            $users = User::findOrFail($finalId)->update([
                'password' => bcrypt($request->password),
            ]);

            $state = [
                'key' => 'changePassword',
                'description' => 'Ha realizado modificación de password',
                'status' => 1,
                'user_id' => $user->id,
            ];

            StateController::state_store($state);

            $data = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $request->password,
            ];

            Mail::send('backend.mails.changePass', $data, function (
                $message
            ) use ($data) {
                $message->subject('Verifique su Email - Notification');
                $message->from('no-reply@aquicreamos.com');
                $message->to($data['email']);
            });
        } else {
            $validator
                ->errors()
                ->add(
                    'old_password',
                    'La informacion no es coherente con nuestras bases de datos'
                );
            Session::flash('snackbar-danger', 'Error');
            return back()
                ->withErrors($validator)
                ->withInput();
        }
        Session::flash('snackbar-success', 'Contraseña cambiada correctamente');
        return back();
    }

    /// Mail
    public function restaurar(Request $request)
    {
        $email = $request->email;
        $card_id = $request->card_id;

        $user = User::where('email', $email)->count();
        $data = Data::where('card_id', $card_id)->count();
        if ($user > 0 && $data > 0) {
            $changue = User::leftJoin('data_users', 'user_id', '=', 'users.id')
                ->where('email', $email)
                ->where('card_id', $card_id)
                ->select('users.id as id')
                ->first();
            User::find($changue->id)->update([
                'password' => bcrypt('LabDiagnosticar@2018'),
            ]);
        }
        Session::flash(
            'snackbar-success',
            'Contraseña modificada Correctamente'
        );
        return back();
    }
}
