<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use Illuminate\Http\Request;
use App\Models\Taxonomies;
use App\Models\DataUser as Data;

use App\Models\State;
use App\Http\Controllers\StateController;
use App\Http\Controllers\NotificationController as Notify; 

use Auth, Session, Carbon\Carbon, DB, DateTime;

class CoursesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:course.update'])->only([
            'update',
            'edit',
        ]);
        $this->middleware(['permission:course.create'])->only([
            'create',
            'store',
        ]);
        $this->middleware(['permission:course.list'])->only(['index']);
        $this->middleware(['permission:course.destroy'])->only(['destroy']);
        $this->middleware(['permission:course.show'])->only([
            'show',
            'courseList',
        ]);
        $this->middleware(['permission:enroll.update'])->only([
            'validateCourse',
        ]);
        $this->middleware(['permission:enroll.create'])->only(['register']);

        //validateCourseDelete - matriculados

        
    }

    public function valArray($type)
    {
        $array = [
            'normal',
            'masterClass',
            'course',
            'tutorial',
            'review',
            'audit',
            'webinar',
            'seminary',
            'conference',
            'webcast',
            'meeting',
            'reading',
            'mooc',
            'spoc',
            'poadcast',
            'video',
            'smallTalk',
        ];

        if (in_array($type, $array)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function index(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $type = $request->type;
            $courses = Courses::where('context', $type)->get();
        } elseif ($request->type == 'all') {
            $type = 'cursos';
            $courses = Courses::all();
        } else {
            $type = 'cursos';
            $courses = [];
        }
        $users = [];
        if (count($courses) > 0) {
            foreach ($courses as $key => $course) {
                $person = Data::where('user_id', $course->subject)
                    ->select('id', 'first', 'last')
                    ->first();
                $listados = DB::table('course_configurate')
                    ->where('courses_id', $course['id'])
                    ->whereNull('contents.deleted_at')
                    ->leftJoin('contents', 'contents_id', '=', 'contents.id')
                    ->get();
                $users[$key] = [
                    'course_id' => $course['id'],
                    'subject' => $person->first . ' ' . $person->last,
                    'listados' => count($listados),
                ];
            }
        }
        $contents = config('paramslist.content:types-unad');
        return Notify::ms(
            'ok',
            201,
            [$type, $courses, $users, $contents],
            'Datos encontrados'
        );
    }

    public function create(Request $request)
    {
        $listFormats = config('paramslist.course:types');
        $type = null;
        if ($this->valArray($request->type) == 1) {
            $type = $request->type;
        }
        return Notify::ms(
            'ok',
            201,
            [$type, $listFormats],
            'Lista de datos encontrados'
        );
    }

    public function store(Request $request)
    {
        $make = $request->all();
        if ($make['state'] == 'draft') {
            $make['state'] = 2;
        } else {
            $make = array_except($make, 'password');
        }

        $make = array_except($make, '_token');
        $make = array_add($make, 'subject', Auth::user()->id);

        if ($request->key_certificate != null) {
            $item = [
                'key_certificate' => str_slug($request->key_certificate, '-'),
                'image_certificate' => $request->image_certificate,
            ];
            $make['json'] = serialize(json_encode($item));

            $make = array_except($make, 'key_certificate');
            $make = array_except($make, 'image_certificate');
        } else {
            $make = array_except($make, 'key_certificate');
            $make = array_except($make, 'image_certificate');
        }

        if ($make['send'] == '0') {
            $make = array_add($make, 'send', null);
        }

        $make = array_add($make, 'rating', 0);
        $make = array_add($make, 'views', 0);

        if ($make['created_at'] != null) {
            $make['created_at'] = Carbon::parse($make['created_at']);
        }

        if ($make['timeOut'] != null) {
            $make['timeOut'] = Carbon::parse($make['timeOut']);
        }

        Courses::create($make);
        return Notify::ms('ok', 201, $make, 'Curso creado correctamente');
    }

    public function add_img()
    {
        $course = Courses::find(1)
            ->files()
            ->create(['files_id' => 1]);
        return Notify::ms(
            'ok',
            201,
            $course,
            'Curso imagen creada correctamente'
        );
    }

    public function edit($id, Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $type = $request->type;
        }
        $taxonomies = Taxonomies::all();
        $course = Courses::where('id', $id)
            ->where('context', $type)
            ->first();
        return Notify::ms(
            'ok',
            201,
            [$type, $course, $taxonomies],
            'Curso listado correctamente'
        );
    }

    public function update(Request $request, $id)
    {
        $make = $request->all();
        if ($make['state'] == 'draft') {
            $make['password'] = $make['password'];
        } else {
            $make = array_except($make, 'password');
        }

        $make = array_except($make, '_method');
        $make = array_except($make, '_token');

        $make = array_add($make, 'subject', Auth::user()->id);

        if ($request->key_certificate != null) {
            $item = [
                'key_certificate' => str_slug($request->key_certificate, '-'),
                'image_certificate' => $request->image_certificate,
            ];
            $make['json'] = serialize(json_encode($item));

            $make = array_except($make, 'key_certificate');
            $make = array_except($make, 'image_certificate');
        } else {
            $make = array_except($make, 'key_certificate');
            $make = array_except($make, 'image_certificate');
        }

        if ($make['send'] == '0') {
            $make = array_add($make, 'send', null);
        }

        if ($make['created_at'] != null) {
            $make['created_at'] = Carbon::parse($make['created_at']);
        }

        if ($make['timeOut'] != null) {
            $make['timeOut'] = Carbon::parse($make['timeOut']);
        }

        $final = Courses::where('id', $id)->update($make);
        Session::flash('snackbar-success', 'Curso editado correctamente');
        return Notify::ms('ok', 201, $final, 'Curso editado correctamente');
        //return back();
    }

    public function destroy(Request $request, $id)
    {
        $force = $request->input('force');
        $options = Courses::findOrFail($id);
        if ($force == 1) {
            $options->forceDelete();
            Session::flash(
                'snackbar-warning',
                'El curso se ha Eliminado totalmente'
            );
            return back();
        }
        $options->delete();
        Session::flash(
            'snackbar-warning',
            'El curso se ha envíado a la papelera'
        );
        return back();
    }

    // muestra el listado de los cursos
    public function courseList(Request $request)
    {
        $courses = Courses::all();
        return Notify::ms('ok', 201, $courses, 'Datos listados correctamente');
    }

    // muestra el curso en especifico y permite registrarse
    public function show(Request $request, $id)
    {
        $course = Courses::where('slug', $id)
            ->orWhere('id', $id)
            ->first();
        if ($course == null) {
            return Notify::ms('no-found', 404, $validator, 'No existe el curso');
        }
        $cdt = Carbon::now();
        $dt = Carbon::now();
        $registered = 0;
        $reviewed = 0;
        // Activa/Inactiva visiblemente el boton de registro
        $ifUserRegister = DB::table('course_user')
            ->where('user_id', Auth::user()->id)
            ->where('courses_id', $course->id)
            ->get();

        //Activa el boton de en proceso de matricula mientras el usuario es matriculado
        if ($ifUserRegister->count() > 0) {
            $registered = 1;
            if ($ifUserRegister[0]->status == 0) {
                $reviewed = 1;
            }
        }

        $contents = DB::table('course_configurate')
            ->leftJoin('contents', 'contents_id', '=', 'contents.id')
            ->select(
                'course_configurate.id',
                'contents_id',
                'contents.content',
                'contents.timeline',
                'contents.slug',
                'contents.type'
            )
            ->orderBy('contents.order', 'asc');

        // Evalua el status actual del contenido
        if (
            !in_array(
                'admin',
                Auth::user()
                    ->getRoleNames()
                    ->toArray()
            )
        ) {
            $contents = $contents
                ->where('contents.status', 1)
                ->where('courses_id', $course->id)
                ->get();
        } else {
            $contents = $contents->where('courses_id', $course->id)->get();
        }

        foreach ($contents as $cont) {
            $explo = explode(':', $cont->timeline);
            $time[] = [(int) $explo[0], (int) $explo[1]];
            $cdt->addHours((int) $explo[0]);
            $cdt->addMinutes((int) $explo[1]);
        }

        $fntime = Carbon::createMidnightDate(2012, 1, 31);
        $fntime->addMinutes((int) $dt->diffInMinutes($cdt));
        $finalTime = $fntime->toTimeString();

        if ($course->timeOut != null && $course->created_at != null) {
            $timeOut = Carbon::parse($course->timeOut);
            $created_at = Carbon::parse($course->created_at);
            $timeActual = Carbon::now();
            $timerOut = 0;
            if ($created_at->equalTo($timeOut)) {
                $timerOut = 0;
            } elseif ($timeActual->greaterThan($timeOut)) {
                $timerOut = 1;
            }
        }

        return Notify::ms(
            'ok',
            201,
            [$course, $registered, $reviewed, $timerOut, $contents, $finalTime],
            'Se a listado correctamente'
        );
    }

    // obtiene los datos del registro y matricula el usuario
    public function register(Request $request)
    {
        if (Auth::check()) {
            $validate = DB::table('course_user')
                ->where('user_id', Auth::user()->id)
                ->where('courses_id', $request->course_id)
                ->get();

            if ($validate->count() == 0) {
                DB::table('course_user')->insert([
                    'courses_id' => $request->course_id,
                    'user_id' => Auth::user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'status' => 0,
                ]);
                return ['msj' => 'ok'];
            } else {
                return ['msj' => 'no'];
            }
        } else {
            return Notify::ms('error', 401, $validator, 'Error en el registro del curso');
        }
    }

    // ver los usuarios si se encuentran matriculados o no y los activa/in
    public function matriculados(Request $request)
    {
        $usuarios = DB::table('course_user')
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftJoin('courses', 'courses.id', '=', 'courses_id')
            ->select(
                'course_user.id as id',
                'users.id as user_id',
                'users.name',
                'courses.id as course_id',
                'courses.course',
                'course_user.status as status_course'
            )
            ->orderBy('courses.course', 'asc')
            ->get();
        return Notify::ms('ok', 201, $usuarios, 'Se a encontrado el Registro');
    }

    // recibe los datos de la activación y realiza un update de los mismos
    public function validateCourse($id)
    {
        DB::table('course_user')
            ->where('id', $id)
            ->update([
                'status' => 1,
            ]);

        return Notify::ms('ok', 201, [], 'Usuario Matriculado correctamente');
    }

    public function validateCourseDelete(Request $request, $id)
    {
        DB::table('course_user')
            ->where('id', $id)
            ->delete();

        return Notify::ms('ok', 201, [], 'Usuario Eliminado correctamente');
        // return back();
    }

    public function rattingCourse($id, Request $request)
    {
        $realizado = State::where('stateable', $request->course_id)
            ->where('user_id', $id)
            ->get();
        $course = Courses::where('id', $request->course_id)->update([
            'rating' => (int) State::where('key', 'rating_course')->avg(
                'value'
            ),
        ]);

        $state = [
            'key' => 'rating_course',
            'stateable' => $request->course_id,
            'value' => $request->value,
            'description' =>
                'Curso: ' .
                $request->course_id .
                ' Calificación: ' .
                $request->value .
                ' ' .
                $request->description,
            'status' => 1,
            'user_id' => $id,
        ];
        $result = null;
        if (count($realizado) > 0) {
            $result = StateController::state_update($realizado[0]->id, $state);
        } else {
            $result = StateController::state_store($state);
        }

        if ($result == 'ok') {
            return [
                'msj' => 'ok',
                'url' =>
                    '/backend/notas/certificacion/' .
                    $id .
                    '?course=' .
                    $request->course_id .
                    '&calificar=0',
            ];
        } else {
            return [
                'msj' =>
                    'La calificación ha fallado porfavor intentelo más tarde',
            ];
        }
    }

    public function viewsCourse($id, Request $request)
    {
        Courses::where('id', $id)->update([
            'view' => $request->view,
        ]);

        $state = [
            'key' => 'view_course',
            'description' => 'Visito el curso:' . $id,
            'url' => $id,
            'status' => 1,
            'user_id' => $request->user_id,
        ];

        StateController::state_store($state);
    }
}
