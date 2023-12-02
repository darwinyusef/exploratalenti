<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Courses;
use App\Models\User;
use App\Models\DataUser as Data;
use App\Http\Controllers\StateController;
use App\Http\Controllers\NotificationController as Notify; 

use Illuminate\Http\Request;

use Validator, Auth, Session, Hash, Mail;

class AuthController extends Controller
{
    public function sendVerifyMail($make, $ms)
    {
        $token = User::find($make->id);

        $data = [
            'name' => $token->name,
            'email' => $make->email,
            'password' => $ms['password'],
            'user' => $make->id,
            'remember_token' => $token->validate_token,
            'url' =>
                env('APP_URL') .
                '/verifyemail?type=&token=' .
                $token->validate_token .
                '&email=' .
                $make->email .
                '&live=' .
                $make->id,
        ];

        if ($ms['type'] == null) {
            Mail::send('backend.mails.registerEmailNew', $data, function (
                $message
            ) use ($data) {
                $message->subject('Verifique su Email - Notification');
                $message->from('no-reply@aquicreamos.com');
                $message->to($data['email']);
            });
        } elseif ($ms['type'] == 'manual') {
            Mail::send('backend.mails.registerEmailNew', $data, function (
                $message
            ) use ($data) {
                $message->subject('Verifique su Email - Notification');
                $message->from('no-reply@aquicreamos.com');
                $message->to($data['email'])->cc('wsgestor@gmail.com');
            });
        }

        $state = [
            'key' => 'registerNew',
            'description' => 'Ha enviado verificación del email',
            'status' => 1,
            'user_id' => $make->id,
        ];

        StateController::state_store($state);

        $fail = Mail::failures();
        if (isset($fail)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function acceptedEmail(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $data = 0;
        if ($user->validate_token == $request->token) {
            $users = User::findOrFail($request->input('live'))->update([
                'active' => 1,
                'status' => 1,
                'validate_token' => null,
            ]);

            $state = [
                'key' => 'acceptedEmail',
                'description' => 'Ha aceptado la verificación del email',
                'status' => 1,
                'user_id' => $user->id,
            ];

            StateController::state_store($state);

            $data = [
                'name' => $user->name,
                'email' => $user->email,
            ];

            Mail::send('backend.mails.welcome', $data, function ($message) use (
                $data
            ) {
                $message->subject('Verifique su Email - Notification');
                $message->from('no-reply@aquicreamos.com');
                $message->to($data['email']);
            });

            Auth::login($user);
        }
        if ($request->input('type') == 'manual') {
            return redirect('/backend/home');
        } else {
            return redirect('/login');
        }
    }

    public function exist($request)
    {
        $validateUser = User::where('email', $request->email)->first();
        $make = $request->all();
        if ($validateUser) {
            // Activa el usuario si se encuentra en bd y si no se a validado aun el email
            if ($validateUser->remember_token != null) {
                $user = $validateUser;
                $verify = $this->sendVerifyMail($user);
                if ($verify == 0) {
                    $users = User::findOrFail($user->id)->update([
                        'status' => 1,
                    ]);
                }
                return ['/backend/home', 'verify'];
                // Envia el detalle al usuario activo / validado
            } else {
                Auth::login($validateUser);

                if ($make['type'] == 'manual') {
                    return ['/backend/home', 'verify'];
                } else {
                    return ['none'];
                }
            }
        } else {
            //crea un usuario nuevo y lo envia a validación
            $make = $request->all();

            $name = $make['first'] . ' ' . $make['last'];
            $make = array_add($make, 'display_name', $name);
            $slug = str_slug($request->name, '-');
            $make = array_add($make, 'name', $name);
            $make = array_add($make, 'slug', $slug);
            $make = array_add($make, 'status', 0);
            $make = array_add($make, 'remember_token', str_random(40));
            $make['password_all'] = $make['password'];
            // se carga la solicitud

            $resSave = $this->guardarAuth($make);
            $verify = $this->sendVerifyMail($resSave[1], $resSave[0]);
            if ($verify == 0) {
                $users = User::findOrFail($user->id)->update([
                    'status' => 1,
                ]);
            }
            Session::flash(
                'snackbar-danger',
                'El email no fue enviado correctamente'
            );
        }
        //
        return ['/backend/home', 'verify'];
    }

    public function guardarAuth($request)
    {
        $institution = Company::find($request['institution'])->company;

        $solicitud = [
            'card_id' => $request['card_id'],
            'email' => $request['email'],
            'first' => htmlentities(
                preg_replace('/[^\p{L}\p{N}\s]/u', '', $request['first'])
            ),
            'last' => htmlentities(
                preg_replace('/[^\p{L}\p{N}\s]/u', '', $request['last'])
            ),
            'phone1' => $request['phone1'],
            'phone2' => $request['phone2'],
            'name' => strtolower($request['display_name']),
            'display_name' => $request['display_name'],
            'institution' => $institution,
            'department' => htmlentities(
                preg_replace('/[^\p{L}\p{N}\s]/u', '', $request['department'])
            ),
            'city' => htmlentities(
                preg_replace('/[^\p{L}\p{N}\s]/u', '', $request['city'])
            ),
            'address' => $request['address'],
            'type' => $request['typecourse'],
            'password' => $request['password_all'],
        ];

        $name = $solicitud['first'] . ' ' . $solicitud['last'];
        $slug = str_slug($name, '-');
        $solicitud = array_add($solicitud, 'name', $name);
        $solicitud = array_add($solicitud, 'status', 1);
        $solicitud = array_add($solicitud, 'slug', $slug);
        $solicitud = array_add($solicitud, 'active', 0);
        $solicitud = array_add($solicitud, 'pago', 0);
        $solicitud = array_add($solicitud, 'theme', 'skin-blue');
        $solicitud = array_add($solicitud, 'validate_token', str_random(40));
        $solicitud['password'] = bcrypt($request['password_all']);
        $solicitud['company_id'] = $request['institution'];
        $solicitud['mobile'] = $request['phone1'];
        $solicitud['phone_home'] = $request['phone2'];
        $solicitud['type_card'] = 'CC';
        $solicitud['typecourse'] = $request['typecourse'];

        $user = User::create($solicitud);
        // se carga la solicitud
        $solicitud = array_add($solicitud, 'user_id', $user->id);
        $solicitud = array_except($solicitud, ['typecourse']);
        Data::create($solicitud);
        $user->assignRole('estudiante');
        return [
            [
                'card_id' => (int) $solicitud['card_id'],
                'name' => $solicitud['first'] . ' ' . $solicitud['last'],
                'email' => $solicitud['email'],
                'password' => $request['password_all'],
                'url' => env('APP_URL') . '/login',
                'type' => null,
            ],
            $user,
        ];
    }

    public function reg_create()
    {
        $companies = Company::all();
        Notify::ms('ok', 201, $companies); 
    }

    public function reg_store(Request $request)
    {
        $data = $request->toArray();
        $find = Data::where('card_id', $request->card_id)->count();

        if ($find > 0) {
            return redirect(
                'http://diagnosticar.com.co/moodle/register/oldresponseuser.php'
            );
        }

        $validator = Validator::make($data, User::$rules);
        if ($validator->fails()) {
            Session::flash('error-info', 'Problema al cargar la información:');
            return redirect('/registro')
                ->withErrors($validator)
                ->withInput();
        } else {
            $action = $this->exist($request);

            if ($action[1] == 'verify') {
                return redirect('/backend');
            }

            Session::flash('error-info', 'Problema al cargar la información:');
            return redirect('/backend');
        }
    }
}
