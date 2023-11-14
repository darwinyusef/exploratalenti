<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Options;
use App\Entities\Post;
use App\Entities\Links;
use App\Entities\Laboratories;
use Mail;

class IndexPageController extends Controller {

    public function index(){
        $options = Options::where('status', '1')->get();
        $porfolios = Post::where('type' , 'portfolio')->take(6)->get();
        $posts = Post::where('type', 'post')->where('state', 'published')->take(6)->get();
        $links = Links::all();
        $laboratories = Laboratories::inRandomOrder()->take(6)->get();
        return view('diagnosticar.index', compact('options', 'porfolios', 'posts', 'links', 'laboratories'));
    }

    public function page(Post $pages){
      $links = Links::all();
      $options = Options::where('status', '1')->get();
      return view('diagnosticar.page', compact('pages', 'options','links'));
    }

    public function post(Post $pages){
      $links = Links::all();
      $options = Options::where('status', '1')->get();
      return view('diagnosticar.page', compact('pages', 'options','links'));
    }

    public function laboratories(Request $request, Post $pages){
      $options = Options::where('status', '1')->get();
      $links = Links::all();
      
      $laboratories = Laboratories::inRandomOrder()->paginate(9);
      if(isset($request->exam)){
            $laboratories = Laboratories::where('exam', 'LIKE', '%'.$request->exam.'%')->orWhere('clinical_use', 'LIKE', '%'.$request->exam.'%')->orderBy('exam', 'asc')->paginate(9);
      }
      return view('diagnosticar.laboratories', compact('pages', 'options','links', 'laboratories'));
    }

    public function laboratory(Post $pages, $laboratory){
      $options = Options::where('status', '1')->get();
      $links = Links::all();
      $laboratory = Laboratories::where('slug', $laboratory)->first();
      if ($laboratory == null) {
        abort(404);
      }
      return view('diagnosticar.laboratory', compact('pages', 'options','links', 'laboratory'));
    }

    public function contact(){
      $links = Links::all();
      $options = Options::where('status', '1')->get();
      return view('diagnosticar.contact', compact('options','links'));
    }

    public function contact_store(Request $request){
      $datos = [
        'name' => $request->name,
        'email' =>$request->email,
        'mobile' =>$request->mobil,
        'menssage' =>$request->menssage,
        'asunto' => $request->asunto,
      ];
      /* Se construye el email que pasa los parametros en use con los datos de los email e información de notificaciones */
        Mail::send('diagnosticar.emails.contacto', $datos , function($msj){
          $msj->subject('Contact Email Notification');
          $msj->from('no-reply@diagnosticar.com.co');
          $msj->cc('contacto@diagnosticar.com.co');
        });
        $fail = Mail::failures();
    }

}
