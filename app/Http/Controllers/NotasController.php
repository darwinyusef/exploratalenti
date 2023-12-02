<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Contents;
use App\Models\Courses;
use App\Models\Interaction;
use App\Models\User;
use App\Models\DataUser as Data;
use App\Models\Certificate;
use App\Http\Controllers\StateController;
use App\Models\State;
use App\Http\Controllers\NotificationController as Notify; 

use Auth, Session, Carbon\Carbon, DB, Redirect;

class NotasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:score.create'])->only([
            'notasUserStore',
        ]);
        $this->middleware(['permission:score.user.list'])->only(['notasUser']);
        
    }

    public function notasUser($id)
    {
        $user = User::find($id);
        $usersCourses = DB::table('course_user')
            ->where('user_id', $id)
            ->get();

        $contents = null;
        if ($usersCourses->count() > 0) {
            // Llamamos los cursos inscritos del usuario
            foreach ($usersCourses as $usCourse) {
                $courses = Courses::where('id', $usCourse->courses_id)->get();

                if ($courses->count() > 0) {
                    foreach ($courses as $course) {
                        // llamamos los contenidos del curso
                        $configurates = DB::table('course_configurate')
                            ->where('courses_id', $course->id)
                            ->leftJoin(
                                'contents',
                                'contents_id',
                                '=',
                                'contents.id'
                            )
                            ->select(
                                'course_configurate.id as course_configurate_id',
                                'contents_id',
                                'courses_id',
                                'contents.content as name_content',
                                'contents.value as value_content',
                                'contents.type as type_content'
                            )
                            ->get();

                        foreach ($configurates as $key => $configurate) {
                            // llamamos las activides hechas por el usuario
                            $activities = DB::table('configurates')
                                ->where('courses_id', $configurate->courses_id)
                                ->where('user_id', $id)
                                ->leftJoin(
                                    'contents',
                                    'contents_id',
                                    '=',
                                    'contents.id'
                                )
                                ->leftJoin(
                                    'interactions',
                                    'interactions_id',
                                    '=',
                                    'interactions.id'
                                )
                                ->leftJoin(
                                    'courses',
                                    'courses_id',
                                    '=',
                                    'courses.id'
                                )
                                ->select(
                                    'configurates.id as configurates_id',
                                    'contents_id',
                                    'courses_id',
                                    'contents.content as name_content',
                                    'contents.value as value_content',
                                    'interactions_id',
                                    'user_id',
                                    'contents.type as type_content',
                                    'requiredActivities',
                                    'userActivity',
                                    'configurates.score as score',
                                    'configurates.status as status',
                                    'closed_at',
                                    'interactions_id',
                                    'interactions.response as response_interaction',
                                    'interactions.content as content_interaction',
                                    'interactions.type as type_interaction',
                                    'interactions.value as value_interaction',
                                    'contents.json as json_contents',
                                    'interactions.status as status_interaction'
                                )
                                ->orderBy('contents.order', 'asc')
                                ->get();
                            // Creamos el objeto para que sea registrado en la view
                            foreach ($activities as $activity) {
                                if (
                                    $configurate->contents_id ==
                                    $activity->contents_id
                                ) {
                                    $contents[] = [
                                        'configurate' => true,
                                        'configurates_id' =>
                                            $activity->configurates_id,
                                        'contents_id' => $activity->contents_id,
                                        'interactions_id' =>
                                            $activity->interactions_id,
                                        'courses_id' => $activity->courses_id,
                                        'user_id' => $activity->user_id,
                                        'calification' => $course->calification,
                                        'course_name' => $course->course,

                                        'requiredActivities' =>
                                            $activity->requiredActivities,
                                        'userActivity' =>
                                            $activity->userActivity,
                                        'score' => $activity->score,
                                        'status' => $activity->status,
                                        'name_content' =>
                                            $activity->name_content,
                                        'value_content' =>
                                            $activity->value_content,

                                        'response_interaction' =>
                                            $activity->response_interaction,
                                        'content_interaction' =>
                                            $activity->content_interaction,
                                        'value_interaction' =>
                                            $activity->value_interaction,
                                        'type_interaction' =>
                                            $activity->type_interaction,
                                        'json_contents' =>
                                            $activity->json_contents,
                                        'status_interaction' =>
                                            $activity->status_interaction,
                                        'archivos' => Interaction::find(
                                            $activity->interactions_id
                                        )->files,
                                        'notaPercent' =>
                                            $activity->score != null ||
                                            $activity->score != 0
                                                ? ($activity->score * 100) /
                                                    (int) $activity->value_content
                                                : null,
                                        'notaCalification' =>
                                            $activity->score != null ||
                                            $activity->score != 0
                                                ? ($activity->score * 5) /
                                                    (int) $activity->value_content
                                                : null,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        } else {
            Session::flash(
                'snackbar-danger',
                'El estudiante no cuenta acciones en el portal'
            );
            return Notify::ms(
                'no-found',
                201,
                [],
                'El estudiante no cuenta acciones en el portal'
            );
            //  return abort(404);
        }
        // view: backend.notas.calificar
        return Notify::ms(
            'ok',
            201,
            [$contents, $user, $courses],
            'Se a listado correctamente'
        );
    }

    public function notasUserStore(Request $request)
    {
        // Valida si existen actividades con información igual a la enviada
        $configurates = DB::table('configurates')
            ->where('id', $request->id)
            ->get();

        if ($configurates->count() == 0) {
            return ['msj' => 'No se encuentra la actividad desarrollada'];
        }

        // Valida si la actividad es forzada o no
        if ($configurates[0]->status != 2 || $request->force != null) {
            // Status de chargue-note
            $key = 'chargue-note';
            // iniciamos en force null si la respuesta es difertente activa el force true y el status note-force
            $force = null;
            if ($request->force != null) {
                if ($request->force != $request->id) {
                    return ['msj' => 'Es necesario forzar el contenido'];
                } else {
                    $key = 'note-force';
                    $force = 1;
                }
            }

            // valida las actividades del tutor si se encuentran completas vs userActivity si es true activa 'calificado' text|image|file|close|calificado
            $selection = [];
            if ($configurates[0]->tutorActivity != null) {
                $selection = explode('|', $configurates[0]->tutorActivity);

                if (!in_array('calificado', $selection)) {
                    if ($force == 1) {
                        array_push($selection, 'force');
                    }
                    array_push($selection, 'calificado');
                }
            } else {
                $selection = explode('|', $configurates[0]->userActivity);
                if (!in_array('calificado', $selection)) {
                    array_push($selection, 'calificado');
                }
            }

            $selection = implode('|', $selection);
            if ($request->nota_id != null) {
                // Crea un update en configurates con la selección status 2 y el score de la nota
                DB::table('configurates')
                    ->where('id', $request->id)
                    ->update([
                        'score' => (int) $request->nota_id,
                        'status' => 2,
                        'tutorActivity' => $selection,
                        'tutor_id' => Auth::user()->id,
                    ]);

                // Crea un update en Interaction con la selección status 4 y el value de la nota
                Interaction::find($configurates[0]->interactions_id)->update([
                    'value' => (int) $request->nota_id,
                    'force' => $request->force == null ? null : $force,
                    'status' => 4,
                ]);

                // hace un search del usuario y extrae los datos necesarios para la creación u actualización del certificado
                $user = User::where('users.id', $configurates[0]->user_id)
                    ->leftJoin(
                        'data_users',
                        'users.id',
                        '=',
                        'data_users.user_id'
                    )
                    ->select(
                        'first',
                        'last',
                        'email',
                        'users.id as id',
                        'data_users.id as id_data',
                        'card_id'
                    )
                    ->first();

                // hace un search del certificado donde obtiene solo el user y el course activo
                $certificate = Certificate::where('iduser', $user->id)
                    ->where('idcourse', $configurates[0]->courses_id)
                    ->first();

                // inicializamos lista que se llenará si se crea el certificado como nuevo permitiendo hacer o no update
                $lista = null;

                // si el search $certificate es null crea de cero el certificado con status 0
                if ($certificate == null) {
                    // se inserta por primera vez la nota
                    $arrayNota = [
                        [
                            'id' => $request->id,
                            'nota' => (int) $request->nota_id,
                        ],
                    ];
                    // se serializa como txt
                    $arrayNota = serialize(json_encode($arrayNota));

                    $dataUser = Data::where('user_id', $user->id)->first();
                    $certificateData = [
                        'iduser' => $user->id,
                        'idcourse' => $configurates[0]->courses_id,
                        'value' => null,
                        'status' => 0,
                        'configurates' => null,
                        'email' => $user->email,
                        'value' => (int) $request->nota_id,
                        'configurates' => $arrayNota,
                        'company_id' => $dataUser->company_id,
                        'cedula' => $user->card_id,
                        'firstname' => strtoupper($user->first),
                        'lastname' => strtoupper($user->last),
                    ];

                    $lista = Certificate::create($certificateData);

                    $open = Certificate::where('iduser', $user->id)
                        ->where('idcourse', $configurates[0]->courses_id)
                        ->first();
                    $course = Courses::find($configurates[0]->courses_id);

                    $encodeSerialize = serialize(
                        json_encode([
                            'view' =>
                                '/view/certificates/' . dump($open->uuid_text),
                            'pdf' =>
                                '/download/certificates/' .
                                dump($open->uuid_text),
                        ])
                    );

                    $resumes = DB::table('configurates')
                        ->where('user_id', $user->id)
                        ->where('courses_id', $configurates[0]->courses_id)
                        ->where('status', 2)
                        ->select('id', 'score')
                        ->get();

                    if (count($resumes) > 0) {
                        $suma = null;
                        // se suman las notas cargadas en configurates y se obtiene el total
                        foreach ($resumes as $numero) {
                            $suma += $numero->score;
                        }
                        $status = 0;
                        $certificado = null;
                        if ($suma > $course->calification) {
                            $certificado = $encodeSerialize;
                            $status = 1;
                        }

                        $encodeResumes = serialize(json_encode($resumes));

                        Certificate::where('id', $open->id)->update([
                            'url' => $certificado,
                            'value' => $suma,
                            'configurates' => $encodeResumes,
                            'status' => $status,
                        ]);
                    }
                } else {
                    $resumes = DB::table('configurates')
                        ->where('user_id', $user->id)
                        ->where('courses_id', $configurates[0]->courses_id)
                        ->where('status', 2)
                        ->select('id', 'score')
                        ->get();

                    if (count($resumes) > 0) {
                        $suma = 0;
                        // se suman las notas cargadas en configurates y se obtiene el total
                        foreach ($resumes as $numero) {
                            $suma += $numero->score;
                        }
                        $status = 0;
                        $courset = Courses::find($configurates[0]->courses_id);
                        if ($suma > $courset->calification) {
                            $status = 1;

                            // se crea el state
                            $state = [
                                'key' => 'certificate',
                                'description' => 'Certificado',
                                'status' => 1,
                                'stateable' => $configurates[0]->courses_id,
                                'user_id' => $configurates[0]->user_id,
                            ];

                            StateController::state_store($state);

                            $data = [
                                'email' => $user->email,
                                'name' => $user->first . ' ' . $user->last,
                            ];
                            //validateUser
                            \Mail::send(
                                'backend.mails.certificado',
                                $data,
                                function ($message) use ($data) {
                                    $message->subject(
                                        'Activacion de Usuario - Notification'
                                    );
                                    $message->from(
                                        'no-reply@inapayudaspedagogicas.com.co'
                                    );
                                    $message
                                        ->to($data['email'])
                                        ->cc(
                                            'laboratorioclinicodiagnosticar@hotmail.com'
                                        );
                                }
                            );
                        }

                        $encodeResumes = serialize(json_encode($resumes));

                        Certificate::where('iduser', $user->id)
                            ->where('idcourse', $configurates[0]->courses_id)
                            ->update([
                                'value' => $suma,
                                'configurates' => $encodeResumes,
                                'status' => $status,
                            ]);
                    }

                    $state = [
                        'key' => $key,
                        'description' =>
                            'Nota: ' .
                            (int) $request->nota_id .
                            ' Actividad: ' .
                            (int) $request->nota_name,
                        'stateable' => $configurates[0]->courses_id,
                        'status' => 1,
                        'user_id' => $configurates[0]->user_id,
                    ];

                    StateController::state_store($state);

                    return [
                        'msj' => 'ok',
                        'url' => '/backend/notas/' . $configurates[0]->user_id,
                    ];
                }
            } else {
                return Notify::ms('ok', 201, [], 'Requiere una nota correcta');
            }
        } else {
            return Notify::ms(
                'ok',
                201,
                [],
                'Ya cuenta con nota activa - debe forzar el cambio'
            );
        }
    }

    public function certificacion(Request $request, $id)
    {
        $certificacion = Certificate::where('iduser', $id)
            ->where('idcourse', $request->course)
            ->first();
        if ($certificacion != null && $request->course) {
            if ($certificacion->status == 1) {
                $encodeSerialize = serialize(
                    json_encode([
                        'view' =>
                            '/view/certificates/' . $certificacion->uuid_text,
                        'pdf' =>
                            '/download/certificates/' .
                            $certificacion->uuid_text,
                    ])
                );
                Certificate::where('id', $certificacion->id)->update([
                    'url' => $encodeSerialize,
                ]);

                // Mail

                $course = Courses::find($request->course);
                $user = User::find($id);

                $statesCount = State::where('user_id', $id)
                    ->where('key', 'rating_course')
                    ->count();
                $countState = State::where('key', 'rating_course')->count();
                $states = State::where('user_id', $id)->get();
                $avg = (int) State::where('key', 'rating_course')->avg('value');
                // si califica
                $calification = 1;
                if ($statesCount == 0 || $request->calificar == 0) {
                    // no califica
                    $calification = 0;
                }

                if (Auth::user()->hasRole('admin')) {
                    $calification = 0;
                }
                // view: backend.notas.certificacion
                return Notify::ms(
                    'ok',
                    201,
                    [
                        $certificacion,
                        $course,
                        $user,
                        $countState,
                        $calification,
                        $states,
                        $avg,
                    ],
                    'Se a listado correctamente'
                );
            } else {
                Session::flash('snackbar-danger', '');
                return Notify::ms(
                    'ok',
                    201,
                    [],
                    'El estudiante no cuenta actualmente con una certificación del curso vigente'
                );
            }
        } else {
            return Notify::ms(
                'ok',
                201,
                $final,
                'El estudiante no cuenta actualmente con una certificación del curso vigente'
            );
        }
    }
}
