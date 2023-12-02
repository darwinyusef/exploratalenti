<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Options;
use App\Models\Post;
use App\Models\Links;
use App\Models\Laboratories;
use App\Http\Controllers\NotificationController as Notify; 

use Mail;

class IndexPageController extends Controller
{
    public function index()
    {
        $options = Options::where('status', '1')->get();
        $porfolios = Post::where('type', 'portfolio')
            ->take(6)
            ->get();
        $posts = Post::where('type', 'post')
            ->where('state', 'published')
            ->take(6)
            ->get();
        $links = Links::all();
        $laboratories = Laboratories::inRandomOrder()
            ->take(12)
            ->get();
        // view: diagnosticar.index
        return Notify::ms(
            'ok',
            201,
            [$options, $porfolios, $posts, $links, $laboratories],
            'Se a listado correctamente'
        );
    }

    public function page(Post $pages)
    {
        $links = Links::all();
        $options = Options::where('status', '1')->get();
        // view: diagnosticar.page
        return Notify::ms(
            'ok',
            201,
            [$pages, $options, $links],
            'Se a listado correctamente'
        );
    }

    public function post(Post $pages)
    {
        $links = Links::all();
        $options = Options::where('status', '1')->get();
        return Notify::ms(
            'ok',
            201,
            [$pages, $options, $links],
            'Se a listado correctamente'
        );
    }

    public function laboratories(Request $request, Post $pages)
    {
        $options = Options::where('status', '1')->get();
        $links = Links::all();

        $laboratories = Laboratories::inRandomOrder()->paginate(9);
        if (isset($request->exam)) {
            $laboratories = Laboratories::where(
                'exam',
                'LIKE',
                '%' . $request->exam . '%'
            )
                ->orWhere('clinical_use', 'LIKE', '%' . $request->exam . '%')
                ->orderBy('exam', 'asc')
                ->paginate(9);
        }
        // view: diagnosticar.laboratories
        return Notify::ms(
            'ok',
            201,
            [$pages, $options, $links, $laboratories],
            'Se a listado correctamente'
        );
    }

    public function laboratory(Post $pages, $laboratory)
    {
        $options = Options::where('status', '1')->get();
        $links = Links::all();
        $laboratory = Laboratories::where('slug', $laboratory)->first();
        if ($laboratory == null) {
            return Notify::ms('no-found', 404, $validator, 'No existe el laboratorio');
        }
        // view: diagnosticar.laboratory
        return Notify::ms(
            'ok',
            201,
            [$pages, $options, $links, $laboratory],
            'Se a listado correctamente'
        );
    }

    public function contact()
    {
        $links = Links::all();
        $options = Options::where('status', '1')->get();
        // view: diagnosticar.contact
        return Notify::ms(
            'ok',
            201,
            [$options, $links],
            'Se a listado correctamente'
        );
    }

    public function contact_store(Request $request)
    {
        $datos = [
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobil,
            'menssage' => $request->menssage,
            'asunto' => $request->asunto,
        ];
        /* Se construye el email que pasa los parametros en use con los datos de los email e información de notificaciones */
        Mail::send('diagnosticar.emails.contacto', $datos, function ($msj) {
            $msj->subject('Contact Email Notification');
            $msj->from('no-reply@diagnosticar.com.co');
            $msj->cc('contacto@diagnosticar.com.co');
        });
        $fail = Mail::failures();
    }
}
