<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth, Mail, Redirect, Carbon\Carbon, DB, Log;
use App\Models\User;
use App\Models\Options;
use App\Http\Controllers\NotificationController as Notify; 


class HomeController extends Controller
{
    public function __construct()
    {
        
    }

    public function index()
    {
        $user = Auth::user();

        $options = Options::where('option_key', 'welcome')->first();

        if ($user->validate_token != null || $user->active == 0) {
            return Redirect::to('activate');
        }

        if ($user->hasRole('estudiante')) {
            return redirect('/backend/curso/usuarios/listar');
        }

        if (
            $user->validate_token != null ||
            $user->active == 1 ||
            $user->active == 2
        ) {
            // view: welcome
            return Notify::ms(
                'ok',
                201,
                $options,
                'Se a listado correctamente'
            );
        }
        //view: welcome
        return Notify::ms('ok', 201, $options, 'Se a listado correctamente');
    }

    public function sendValidator($id, Request $request)
    {
        $user = User::where('id', $id)->first();
        if ($user->validate_token != null || $user->active == 0) {
            if (!Auth::check()) {
                return Redirect::to('login');
            }
            if (
                $user->validate_token == $request->input('remember_token') &&
                $request->input('remember_token') != null
            ) {
                User::where('id', $id)->update([
                    'active' => 1,
                    'pago' => 1,
                    'status' => 1,
                    'validate_token' => null,
                ]);
                Auth::login($user);
                return Redirect::to('login');
            }
        }
        if ($user->validate_token == null) {
            User::where('id', $id)->update([
                'active' => 2,
                'pago' => 0,
                'status' => 0,
                'validate_token' => str_slug(rand(10000000, 999999999), ''),
            ]);
        }
        $user2 = User::where('id', $id)->first();
        $data = [
            'name' => $user2->name,
            'email' => $user2->email,
            'password' => null,
            'user' => $user2->id,
            'remember_token' => $user2->validate_token,
            'url' =>
                env('APP_URL') .
                '/verifyemail?type=&token=' .
                $user2->validate_token .
                '&email=' .
                $user2->email .
                '&live=' .
                $user2->id,
        ];

        $fecha = Carbon::now();

        Mail::send('backend.mails.registerEmailNew', $data, function (
            $message
        ) use ($data) {
            $message->subject('Verifique su Email - Notification');
            $message->from('laboratorioclinicodiagnosticar@gmail.com');
            $message
                ->to($data['email'])
                ->cc('wsgestor@gmail.com')
                ->cc('laboratorioclinicodiagnosticar@gmail.com');
        });
        if (count(Mail::failures()) > 0) {
            Log::error(
                'El Email Verficaci��n: ' .
                    $data['email'] .
                    ' del Usuario: ' .
                    $user2->name .
                    ' Contiene un error, Email registrado el ' .
                    $fecha
            );
        } else {
            Log::info(
                'El Email Verficaci��n: ' .
                    $data['email'] .
                    ' del Usuario: ' .
                    $user2->name .
                    ' Fue enviado Correctamente Email registrado el ' .
                    $fecha
            );
        }
        return redirect('activate')->with('user', $id);
    }

    public function pago()
    {
        if (!Auth::check()) {
            return Redirect::to('login');
        }
        $user = Auth::user();
        $data = [
            'email' => $user->email,
            'name' => $user->name,
        ];
        $fecha = Carbon::now();
        Mail::send('backend.mails', $data, function ($message) use ($data) {
            $message->subject('Activación de Usuario - Notification');
            $message->from('laboratorioclinicodiagnosticar@gmail.com');
            $message
                ->to($data['email'])
                ->cc('wsgestor@gmail.com')
                ->cc('laboratorioclinicodiagnosticar@gmail.com');
        });

        if (count(Mail::failures()) > 0) {
            Log::error(
                'El Email Activacion: ' .
                    $data['email'] .
                    ' del Usuario: ' .
                    $data['name'] .
                    ' Contiene un error, Email registrado el' .
                    $fecha
            );
        } else {
            Log::info(
                'El Email Activacion: ' .
                    $data['email'] .
                    ' del Usuario: ' .
                    $data['name'] .
                    ' Fue enviado Correctamente Email registrado el' .
                    $fecha
            );
        }
        // view: auth.login
        return Notify::ms('ok', 201, $data, 'Se a listado correctamente');
    }

    public function chargueip()
    {
        $ip = null;
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $data = [
            'ip' => $ip,
            'users_id' => Auth::user()->id,
        ];
        $d = IpUser::create($data);
        return Redirect::to('home');
    }

    public function getip($id)
    {
        $ips = IpUser::where('users_id', $id)->get();
        if (Auth::user()->hasRole('administrador')) {
            $ips = IpUser::all();
        }
        return Notify::ms('ok', 201, $ips, 'Se a listado correctamente');
        // view: admin.getip
    }

    /* Elementos para el cargue

    use App\Http\Controllers\HomeController as Move;
    //'changeState','assigned','contact','user','timeService','createComment','create','edit','delete'
    Move::chargue('Entro a pregunta', 'move', 'assigned');

*/
    public static function chargue($text, $reasponse, $typeMove)
    {
        $chargue = [
            'archive' => $text,
            'reasponse' => $reasponse,
            'type' => $typeMove,
            'active' => 1,
            'users_id' => Auth::user()->id,
        ];
        Makeuser::create($chargue);
    }

    public function chargueUser()
    {
        $makers = Makeuser::paginate(350);
        //view: admin.makeuser
        return Notify::ms('ok', 201, $makers, 'Se a listado correctamente');
    }
}
