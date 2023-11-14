<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Laboratories;
use App\Entities\Company;
use App\Entities\Certificate;
use App\Entities\State;
use App\Entities\DataUser as Data;
use App\Entities\User;
use Auth, Validator, Redirect, Session;
use Carbon\Carbon;

class LaboratoriesController extends Controller
{
  public function __construct()
  {
    //$this->middleware('auth', ['except' => ['moodle_download', 'moodle_certificate', 'moodle_view', 'moodle_download_pdf']]);
    $this->middleware('auth');
  }
  public function index()
  {
    $laboratories = Laboratories::all();
    return view('backend.laboratory.list', compact('laboratories'));
  }

  public function create()
  {
    return view('backend.laboratory.create');
  }

  public function reportCompanyUser(Request $request)
  {
    $page = $request->page;
    $total = $request->total;
    $email = $request->email;
    $cedula = $request->cedula;

    if ($total) {
      $total = $total;
    } else {
      $total = 30;
    }

    if (Auth::user()->hasRole('admin')) {
      $lists = User::leftJoin('data_users', 'user_id', "=", "users.id")
        ->leftJoin('companies', 'data_users.company_id', "=", "companies.id")
        ->orderBy('users.id', 'asc')
        ->select(
          'first',
          'last',
         // 'course_state',
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
        $lists = $lists->where('email', 'LIKE', '%' . $email . '%');
      } else {
        $lists = $lists;
      }

      if ($cedula) {
        $lists = $lists->where('card_id', $cedula);
      } else {
        $lists = $lists;
      }

      $lists = $lists->get();
      foreach ($lists as $key => $list) {
        $users[] =  [
          "first" =>  $list->first,
          "last" =>  $list->last,
    //      "course_state" => $list->course_state,
          "email" => $list->email,
          "company" => $list->company,
          "id" => $list->id,
          "company_id" => $list->company_id,
          "id_data" => $list->id_data,
          "mobile" => $list->mobile,
          "address" => $list->address,
          "type_card" => $list->type_card,
          "card_id" => $list->card_id,
          "neighborhood" => $list->neighborhood,
          "count-list" => count($list->state) > 0 ? 1 : 0,
          "list" => count($list->state) > 0 ? $list->state : 0,
        ];
      }
    } else {
      $companies = Company::where('id', Auth::user()->datauser()->first()->company_id)->orWhere('parent', Auth::user()->datauser()->first()->company_id)->get();
      foreach ($companies as $comp) {
        $lists = User::leftJoin('data_users', 'user_id', "=", "users.id")
          ->leftJoin('companies', 'data_users.company_id', "=", "companies.id")
          ->orderBy('users.id', 'asc')
          ->select(
            'first',
            'last',
       //     'course_state',
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
          )->where('data_users.company_id', $comp->id);

        if ($email) {
          $lists = $lists->where('email', 'LIKE', '%' . $email . '%');
        } else {
          $lists = $lists;
        }

        if ($cedula) {
          $lists = $lists->where('card_id', $cedula);
        } else {
          $lists = $lists;
        }

        $lists = $lists->get();
        foreach ($lists as $key => $list) {
          $users[] =  [
            "first" =>  $list->first,
            "last" =>  $list->last,
           // "course_state" => $list->course_state,
            "email" => $list->email,
            "company" => $list->company,
            "id" => $list->id,
            "company_id" => $list->company_id,
            "id_data" => $list->id_data,
            "mobile" => $list->mobile,
            "address" => $list->address,
            "type_card" => $list->type_card,
            "card_id" => $list->card_id,
            "neighborhood" => $list->neighborhood,
            "count-list" => count($list->state) > 0 ? 1 : 0,
            "list" => count($list->state) > 0 ? $list->state : 0,
          ];
        }
      }
    }

    $count = count($users) / $total + 1;
    $users = collect($users)->reverse('id')->forPage($page, $total);
    return view('backend.user.user-list-company', compact('users', 'count'));
  }

  public function store(Request $request)
  {
    //Se transforma la socitud a array y se limpia para enviar
    $data = $request->toArray();
    $data = array_add($data, 'slug', str_slug($data['exam'], '-'));
    $data = array_add($data, 'user_id', Auth::id());
    $validator = Validator::make($data, Laboratories::$rules);
    if ($validator->fails()) {
      return back()->withErrors($validator)
        ->withInput();
    } else {
      // se carga la solicitud
      Laboratories::create($data);
      Session::flash('snackbar-success', 'Se a creado un nuevo Laboratorio');
      return Redirect::to('/backend/laboratorios');
    }
  }

  public function edit($id)
  {
    $laboratory = Laboratories::findOrFail($id);
    return view('backend.laboratory.update', compact('laboratory'));
  }

  public function update(Request $request, $id)
  {
    $data = $request->toArray();
    $data = array_add($data, 'slug', str_slug($data['exam'], '-'));
    $data = array_add($data, 'user_id', Auth::id());
    $data = array_except($data, ['_method']);
    $data = array_except($data, ['_token']);
    //se modifican las entradas on x 1 para que se valide el bolean
    $validator = Validator::make($data, Laboratories::$rules);
    if ($validator->fails()) {
      //return $validator->errors()->toArray();
      return redirect('/backend/laboratorios/' . $id . '/edit')
        ->withErrors($validator)
        ->withInput();
    } else {
      // se carga la solicitud
      Laboratories::where('id', $id)->update($data);
      Session::flash('snackbar-success', 'Se a Editado el Registro');
      return Redirect::to('/backend/laboratorios');
    }
  }

  public function destroy(Request $request, $id)
  {
    $force = $request->input('force');
    $group = Laboratories::findOrFail($id);

    if ($force == 1) {
      $group->forceDelete();
      return Redirect::to('/backend/laboratorios');
    }
    $group->delete();
    Session::flash('snackbar-warning', 'Se a Elimninado el Registro');
    return redirect('/backend/laboratorios');
  }

  public function moodle(Request $request)
  {
    dd('actualmente deprecated');
    $page = $request->page;
    $total = $request->total;
    $user = $request->user;
    $cedula = $request->cedula;
    $empresa = $request->empresa;

    if ($total) {
      $total = $total;
    } else {
      $total = 30;
    }
    $url = "http://diagnosticar.com.co/moodle/register/json.php";
    $json = file_get_contents($url);
    $obj = json_decode($json);
    $count = count($obj) / $total + 1;

    if (isset($user)) {
      $obj = collect($obj)->filter(function ($value, $key) use ($user) {
        if (strpos($value->firstname, $user) !== false) {
          return $value;
        }
      });
    } else if (isset($cedula)) {
      $obj = collect($obj)->filter(function ($value, $key) use ($cedula) {
        if (strpos($value->idnumber, $cedula) !== false) {
          return $value;
        }
      });
    } else if (isset($empresa)) {
      $obj = collect($obj)->filter(function ($value, $key) use ($empresa) {
        if (strpos($value->institution, $empresa) !== false) {
          return $value;
        }
      });
    } else {
      $obj = collect($obj)->reverse('id')->forPage($page, $total);
    }
    $company = Company::all();
    foreach ($obj as $value) {
      $tot = User::where('email', $value->email)->count() > 0;
      $value->icq = $tot;
    }
    return view('backend.laboratory.new_users', compact('obj', 'count', 'company'));
  }

  public function list_mail_moodle(Request $request)
  {
    dd('actualmente deprecated');
    $in = $request->in;
    $out = $request->out;
    $url = "http://diagnosticar.com.co/moodle/register/json_transfer.php?in=1&out=" . $out;
    $json = file_get_contents($url);
    $obj = json_decode($json);


    foreach ($obj as $key => $email) {
      $em[] = $email->email;
    }

    foreach ($obj as $key => $idnumber) {
      if ($idnumber->idnumber != "") {
        $ident[] = $idnumber->idnumber;
      }
    }
    $resultado = array_diff($em, array_diff(array_unique($em), array_diff_assoc($em, array_unique($em))));
    $resultadoid = array_diff($ident, array_diff(array_unique($ident), array_diff_assoc($ident, array_unique($ident))));

    return view('backend.laboratory.list_duplicate', compact('resultado', 'resultadoid'));
  }



  /**
   * Actualmente se encuetra deprecated 
  */
  public function chargue_moodle(Request $request)
  {
    dd('actualmente deprecated');
    $in = $request->in;
    $out = $request->out;
    $url = "http://diagnosticar.com.co/moodle/register/json_transfer.php?in=" . $in . "&out=" . $out;
    $json = file_get_contents($url);
    $obj = json_decode($json);

    foreach ($obj as $key => $email) {
      $em[] = $email->email;
    }
    $resultado[] = array_diff($em, array_diff(array_unique($em), array_diff_assoc($em, array_unique($em))));
    // All Arrays IDS UNIQUE
    /*foreach(array_unique($resultado) as $v) {
              $lists[] = array_keys($resultado, $v);
          }*/

    $duplicates = 0;
    $data = [];
    foreach ($obj as $usernew) {
      foreach ($resultado as $val) {
        if ($val == $usernew->email) {
          $data[] = $usernew->email;
        }
        $data = array_unique($data);
      }

      if (!in_array($usernew->email, $data)) {
        $list = [];
        $tipo = null;
        $identify = trim(preg_replace("/[^A-Za-z0-9 ]/", '', $usernew->idnumber), " ");
        if ($identify != "" && is_numeric($identify)) {
          $usernew->idnumber = $identify;
          $name = ucwords($usernew->firstname) . ' ' . ucwords($usernew->lastname);
          $slug = str_slug($name, '-');
          $institution = explode(';', $usernew->institution);
          $newUser = [
            'name' => $usernew->username,
            'email' => $usernew->email,
            'password' => bcrypt('LabDiagnosticar@2019'),
            'status' => 1,
            'validate_token' => NULL,
            'active' => 1,
            'slug' => $slug,
            'display_name' => $name,
            'city' => $usernew->city,
            'type' => $usernew->typecourse,
     //       'course_state' => $usernew->certificate,
          ];
          $institute = null;
          if (is_numeric($institution[0])) {
            $institute = $institution[0];
          }
          $newdatauser = [
            'first' => ucwords($usernew->firstname),
            'last' => ucwords($usernew->lastname),
            'card_id' => $identify,
            'type_card' => 'CC',
            'mobile' => $usernew->phone1,
            'phone_home' => $usernew->phone2,
            'address' => $usernew->address,
            'company_id' => $institute,
            'course_id' => 3
          ];



          if (!User::where('email', '=', $usernew->email)->exists()) {
            $list[] = $newUser;
            $emailErrors = ['result' => true];
            $validator = Validator::make(collect($newUser)->all(), [
              'email' => 'required|unique:users',
            ]);
            if ($validator->fails()) {
              $emailErrors = ['error' => $validator->errors()->first('email'), 'email' => $usernew->email, 'result' => false];
            }

            if ($emailErrors['result']) {
              if (!Data::where('card_id', '=', $identify)->exists()) {
                $iddata = User::create($newUser);
                $arraynew = array_add($newdatauser, 'user_id', $iddata->id);
                Data::create($arraynew);
                $iddata->assignRole('estudiante');
              }
            }
          } //User Exist

          $newUser = array_except($newUser, ['password']);
          $iduser = Data::where('card_id', $identify)->select('user_id', 'id')->first();
          if ($iduser != null) {
            $usert = User::where('id', $iduser->user_id)->update($newUser);
            Data::where('id', $iduser->id)->update($newdatauser);
          }
        } // Configuración de identidad
      }  // Validación de Duplicidad en Moodle

    } // Foreach  $obj
    return Redirect::to('/backend/usuarios')->with($resultado);
  }



  public function moodle_company()
  { 
    dd('actualmente deprecated');

    if (Auth::user()->hasRole('admin')) {
      $certificate_company =  Certificate::all();
      foreach ($certificate_company as $certificate) {
        $certificates[] = $certificate;
      }
    } else {
      $company =  Company::where('id', Auth::user()->datauser()->first()->company_id)->orWhere('parent', Auth::user()->datauser()->first()->company_id)->select('id', 'parent', 'company')->get();

      foreach ($company as $val) {
        $certificate_company = Certificate::where('company_id', $val->id)->get();
        foreach ($certificate_company as $certificate) {
          $certificates[] = $certificate;
        }
      }
    }

    if (!isset($certificates)) {
      return "No existen actualmente certificados";
    }

    return view('backend.laboratory.certificates_list', compact('certificates'));
  }

  public function moodle_search()
  {
    dd('actualmente deprecated');
    return view('backend.laboratory.search');
  }

  public function moodle_search_result(Request $request)
  {
    dd('actualmente deprecated ');
    $certificates =  Certificate::where('cedula', $request->cedula)->get();
    if (count($certificates) == 0) {
      return abort(404);
    }
    return view('backend.laboratory.company_list', compact('certificates'));
  }

  public function moodle_store(Request $request)
  {
    dd('actualmente deprecated');
    $data = $request->toArray();

    $validator = Validator::make($data, Certificate::$rules);
    if ($validator->fails()) {
      //return $validator->errors()->toJson();
      return [0, $validator->errors()->toJson()];
    } else {
      if ($data['certificate'] == 1) {
        // se carga la solicitud
        $lista = Certificate::create($data);
        $serlialize = serialize(json_encode(['view'  => '/view/certificates/' . dump($lista->uuid_text), 'pdf' => '/download/certificates/' . dump($lista->uuid_text)]));
        Certificate::where('iduser', $data['id'])->update([
          'url' => $serlialize,
        ]);
        $list = [$data['certificate'], 'Certificado Creado'];
      } else {
        $list = [$data['certificate'], 'Estado Actualizado'];
      }
    }
    return $list;
  }

  public function moodle_download_pdf($id)
  {
    dd('actualmente deprecated');
    $data = Certificate::findOrFail($id);
    if ($data->cedula == null) {
      Session::flash('snackbar-warning', 'Se requiere del número de Identificación para el cargue del Certificado');
      return Redirect::to('/backend/moodle');
    }
    if ($data->firstname == null) {
      Session::flash('snackbar-warning', 'Se requiere del número de Primer Nombre para el cargue del Certificado');
      return Redirect::to('/backend/moodle');
    }
    if ($data->lastname == null) {
      Session::flash('snackbar-warning', 'Se requiere del número de Apellido para el cargue del Certificado');
      return Redirect::to('/backend/moodle');
    }

    $course = Data::where('id_usermoodle', $data->iduser)->select('course_id')->first();

    $url = "http://diagnosticar.com.co/moodle/register/get_courses_laravel.php";
    $json = file_get_contents($url);
    $obj = json_decode($json);
    $count = count($obj) / 20 + 1;

    if ($course == null) {
      Session::flash('snackbar-warning', 'Error en la selección del curso del usuario: por favor modifique el curso del usuario');
      return redirect('/search/certificates');
    }
    foreach ($obj as $object) {
      $total = '/assets/img/image305.png';
      if ((int)$object->id == $course->course_id) {
        $total = $object->url;
      }
    }

    $data['certificate'] = env('APP_ENV') . $total;

    \PDF::setOptions(['dpi' => 90, 'defaultFont' => 'Helvetica']);
    $pdf = \PDF::loadView('backend.laboratory.certifica', $data)
      ->setPaper('a4', 'landscape');

    return $pdf->download($id . '.pdf');
  }

  public function moodle_view($id)
  {
    dd('actualmente deprecated');
    $data = Certificate::findOrFail($id);
    if ($data->cedula == null) {
      Session::flash('snackbar-warning', 'Se requiere del número de Identificación para el cargue del Certificado');
      return Redirect::to('/backend/moodle');
    }
    if ($data->firstname == null) {
      Session::flash('snackbar-warning', 'Se requiere del número de Primer Nombre para el cargue del Certificado');
      return Redirect::to('/backend/moodle');
    }
    if ($data->lastname == null) {
      Session::flash('snackbar-warning', 'Se requiere del número de Apellido para el cargue del Certificado');
      return Redirect::to('/backend/moodle');
    }
    $course = Data::where('id_usermoodle', $data->iduser)->select('course_id')->first();
    if ($course == null) {
      Session::flash('snackbar-warning', 'Error en la selección del usuario');
      return redirect('/search/certificates');
    }
    $url = "http://diagnosticar.com.co/moodle/register/get_courses_laravel.php";
    $json = file_get_contents($url);
    $obj = json_decode($json);
    $count = count($obj) / 20 + 1;

    foreach ($obj as $object) {
      $total = '/assets/img/image305.png';
      if ((int)$object->id == $course->course_id) {
        $total = $object->url;
      }
    }

    return view('backend.laboratory.certifica_view', compact('data', 'total'));
  }

  public function moodle_destroy(Request $request, $id)
  {
    dd('actualmente deprecated');
    $force = $request->input('force');
    $group = Certificate::findOrFail($id);

    if ($force == 1) {
      $group->forceDelete();
      return Redirect::to('/backend/moodle');
    }
    $group->delete();
    Session::flash('snackbar-warning', 'Se a Elimninado el Registro');
    return redirect('/backend/moodle');
  }


  public function certificate_moodle(Request $request)
  {
    $url = "http://diagnosticar.com.co/moodle/register/get_courses_laravel.php";
    $json = file_get_contents($url);
    $obj = json_decode($json);
    $count = count($obj) / 20 + 1;

    dd($obj);
    foreach ($obj as $object) {
      if ($object->id != 1) {
        $total[] = $object;
      }
    }
    return view('backend.laboratory.course', compact('total'));
  }


  // Cambia el id del certificado por el que se encuentra registrado en el datausers 
  public function modify_id()
  {
    dd('deprecated');
    $emails = Certificate::select('email', 'id', 'cedula')->get();
    foreach ($emails as $email) {
      $id = User::leftJoin('data_users', 'user_id', "=", "users.id")
        ->where('email', $email->email)->select('data_users.id', 'user_id', 'email', 'data_users.card_id')->first();

      $data = [
       // 'iduser' => $id['id_usermoodle']
      ];

      $certificate[] = Certificate::where('email', $id['email'])->where('cedula', $id['card_id'])->update($data);
    }

    return $certificate;
  }

}
