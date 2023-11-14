<?php

namespace App\Http\Controllers;

use App\Entities\Contents;
use App\Entities\Courses;
use App\Entities\Interaction;
use Illuminate\Http\Request;
use App\Entities\Taxonomies;
use App\Http\Controllers\CoursesController;
use Auth, Session, Carbon\Carbon, DB, Redirect;

class ContentsController extends Controller
{
    public function __construct(CoursesController $coursesValidator)
    {
        $this->courses = $coursesValidator;
        $this->middleware(['permission:content.update'])->only([
            'update',
            'edit',
        ]);
        $this->middleware(['permission:content.create'])->only([
            'create',
            'store',
        ]);
        $this->middleware(['permission:content.show'])->only(['show']);
        $this->middleware(['permission:content.list'])->only(['lista']);
        $this->middleware(['permission:content.destroy'])->only(['destroy']);
        $this->middleware('auth');
    }

    public function valArray($type)
    {
        $array = config('paramslist.content:types');
        if (in_array($type, $array)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function lista($id, Request $request)
    {
        $contents = DB::table('course_configurate')
            ->whereNull('contents.deleted_at')
            ->where('state', 1)
            ->where('contents.status', 1)
            ->leftJoin('contents', 'contents_id', '=', 'contents.id')
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->select(
                'users.name as user_name',
                'user_id',
                'contents_id as id',
                'contents.content as content_name',
                'contents.excerpt as content_excerpt',
                'contents.timeLine',
                'contents.status',
                'contents.timeIn',
                'contents.timeOut',
                'contents.value',
                'contents.type as content_type',
                'contents.slug as content_slug'
            );

        if ($id != 'todos') {
            if ($this->courses->valArray($request->type) == 1) {
                $type = $request->type;
                $course_id = $id;
                $contents = $contents->where('courses_id', $id);
                $contents = $contents->get();
                $courses = Courses::find($id);
            }
        } else {
            $type = null;
            $course_id = null;
            $contents = $contents->where('courses_id', null);
            $contents = $contents->get();
            $courses = [];
        }

        if (count($contents) == 0) {
            return response()->json(
                [
                    'type' => 'ok',
                    'message' => 'No se encontraron',
                    'data' => [],
                ],
                201
            );
        }

        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Lista de datos encontrados para edición',
                'data' => [$type, $contents, $course_id],
            ],
            201
        );
    }

    public function validateType($type, $course_id)
    {
        if ($type == null && $course_id == null) {
            $selection = ['text', 'files', 'image'];
            $eject = [
                'key' => 'abierto',
                'name' => 'Disponible para anexar cualquier contenido',
                'description' => 'Contenido Abierto o disponible',
                'icon' => 'fa-briefcase',
            ];
        } else {
            foreach (config('paramslist.content:types-unad') as $types) {
                if ($types['key'] == $type) {
                    if (str_contains($types['origin'], '|')) {
                        $selection = explode('|', $types['origin']);
                    } else {
                        $selection = [$types['origin']];
                    }
                    $eject = $types;
                }
            }
        }

        return response()->json(
            [
                'type' => 'ok',
                'message' => 'No se encontraron',
                'error' => [$selection, $eject],
            ],
            201
        );
    }

    public function validateRegistro($courseId)
    {
        if ($courseId != null) {
            $registrado = DB::table('course_user')
                ->where('courses_id', (int) $courseId)
                ->where('user_id', Auth::user()->id)
                ->select('id', 'courses_id', 'user_id', 'status')
                ->first();

            if ($registrado) {
                if ($registrado->status != 1) {
                    Session::flash(
                        'snackbar-danger',
                        'Debe esperar el proceso de matrícula'
                    );
                    return response()->json(
                        [
                            'type' => 'ok',
                            'message' => 'No se encontraron',
                            'error' => [$selection, $eject],
                        ],
                        201
                    );
                }
            } else {
                Session::flash('snackbar-danger', 'Debe estar registrado');
                return 'registro';
            }
        }
    }
    public function show(Request $request, $id)
    {
        $allContents = [];
        $interactions = collect([]);

        if (!is_string(rtrim(ltrim($id))) || !is_numeric((int) $id)) {
            Session::flash(
                'snackbar-danger',
                'Existe un problema con el contenido reportelo al administrador'
            );
            return back();
        }

        $content = Contents::where('slug', rtrim(ltrim($id)))
            ->orWhere('id', $id)
            ->first();

        if ($content == null) {
            return abort(404);
        }

        if ($request->course_id != null && $content->count() > 0) {
            $valRegistro = $this->validateRegistro($request->course_id);

            if ($valRegistro == 'matricula') {
                return redirect('/backend/curso/usuarios/listar');
            } elseif ($valRegistro == 'registro') {
                return redirect('/backend/curso/usuarios/listar');
            }

            if ($request->course_id == null) {
                return abort(404);
            }

            $allContents = DB::table('course_configurate')
                ->leftJoin('contents', 'contents_id', '=', 'contents.id')
                ->select(
                    'course_configurate.id',
                    'contents_id',
                    'contents.content',
                    'contents.timeline',
                    'contents.slug',
                    'contents.type'
                )
                ->orderBy('contents.order', 'asc')
                ->where('courses_id', $request->course_id)
                ->get();

            $configurates = DB::table('configurates')
                ->where('courses_id', $request->course_id)
                ->where('contents_id', $content->id)
                ->where('user_id', Auth::user()->id)
                ->get();

            $closed_at = null;
            if (count($configurates) > 0) {
                foreach ($configurates as $conf) {
                    $interactions_id = $conf->interactions_id;
                    $closed_at = $conf->closed_at;
                }
                $interactions = Interaction::find($interactions_id);
            }

            if ($this->valArray($request->type) == 1) {
                $type = $request->type;
            } else {
                $type = null;
            }
            if ($request->course_id) {
                $course_id = $request->course_id;
            } else {
                $course_id = null;
            }

            $course = Courses::find($course_id);
            $tipesUnad = config('paramslist.content:types-unad');
            $typeContents = [];

            foreach ($tipesUnad as $typeContent) {
                if ($typeContent['origin'] == 'text|iframe|image') {
                    $typeContents[0][] = $typeContent['key'];
                }

                if ($typeContent['origin'] == 'url|iframe|file|text') {
                    $typeContents[1][] = $typeContent['key'];
                }

                if ($typeContent['origin'] == 'url|iframe') {
                    $typeContents[2][] = $typeContent['key'];
                }

                if ($typeContent['origin'] == 'file|text') {
                    $typeContents[3][] = $typeContent['key'];
                }
                if ($typeContent['origin'] == 'image') {
                    $typeContents[4][] = $typeContent['key'];
                }

                if ($typeContent['origin'] == 'url|image') {
                    $typeContents[5][] = $typeContent['key'];
                }

                if ($typeContent['origin'] == 'question') {
                    $typeContents[6][] = $typeContent['key'];
                }

                if ($typeContent['origin'] == 'url|iframe|question') {
                    $typeContents[6][] = $typeContent['key'];
                }
            }
            return view(
                'backend.content.only-content',
                compact(
                    'content',
                    'type',
                    'course',
                    'allContents',
                    'interactions',
                    'typeContents',
                    'closed_at'
                )
            );
        } else {
            return abort(404);
        }
    }

    public function create(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $type = $request->type;
            $course_id = $request->id;
        } else {
            $type = null;
            $course_id = null;
        }

        $validate = $this->validateType($type, $course_id);
        $selection = $validate[0];
        $eject = $validate[1];
        $clase = config('paramslist.content:classroom');

        return view(
            'backend.content.create',
            compact('type', 'course_id', 'selection', 'eject', 'clase')
        );
    }

    public function store(Request $request)
    {
        $make = $request->all();

        if ((int) $make['status'] == 2) {
            $make['password'] = $make['password'];
            (int) ($make['status'] = 2);
        } else {
            $make = array_except($make, 'password');
        }
        $make['timeIn'] = '00:00';

        if ($request->question != null) {
            $item = [
                'question' => $request->question,
                'answers' => $request->answers,
                'feedback' => $request->feedback,
            ];
            $make['json'] = serialize(json_encode($item));

            $make = array_except($make, 'question');
            $make = array_except($make, 'answers');
            $make = array_except($make, 'feedback');
        } else {
            $make = array_except($make, 'question');
            $make = array_except($make, 'answers');
            $make = array_except($make, 'feedback');
        }

        $make = array_except($make, '_token');
        $make = array_except($make, 'options');

        if ($make['timeIn'] != null) {
            $make['timeIn'] = Carbon::parse($make['timeIn']);
        }

        if ($make['timeOut'] != null) {
            $make['timeOut'] = Carbon::parse($make['timeOut']);
        }

        $element = Contents::create($make);

        DB::table('course_configurate')->insert([
            'user_id' => Auth::user()->id,
            'courses_id' => $make['course_id'],
            'contents_id' => $element->id,
            'state' => 1,
            'created_at' => Carbon::parse($element->created_at),
            'updated_at' => Carbon::parse($element->updated_at),
        ]);

        Session::flash('snackbar-success', 'Contenido creado correctamente');
        return back();
    }

    public function add_img()
    {
        $content = Contents::find(1)
            ->files()
            ->create(['files_id' => 1]);
        Session::flash('snackbar-success', 'Contenido creado correctamente');
        return $content;
    }

    public function edit($id, Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $type = $request->type;
            $course_id = $request->course_id;

            $max = Courses::find($course_id)
                ->select('calification')
                ->first();
        } else {
            $max = null;
            $type = null;
            $course_id = null;
        }
        $validate = $this->validateType($type, $course_id);
        // Elementos que amplian la descripción del tipo de actividad esta se encuentra en config
        $selection = $validate[0];
        $eject = $validate[1];
        $clase = config('paramslist.content:classroom');

        $taxonomies = Taxonomies::all();

        if ($type == null) {
            return abort(404);
        }
        $taxonomies = Taxonomies::all();

        $content = Contents::where('id', $id);

        if ($type != null) {
            $content = $content->where('type', $type)->first();
        } else {
            $content = $content->first();
        }

        if ($content == null) {
            return abort(404);
        }

        return view(
            'backend.content.update',
            compact(
                'type',
                'content',
                'max',
                'taxonomies',
                'course_id',
                'selection',
                'eject',
                'clase'
            )
        );
    }

    public function update(Request $request, $id)
    {
        $make = $request->all();
        if ($make['status'] == 'draft') {
            $make['password'] = $make['password'];
            $make = array_except(1, 'status');
        } else {
            $make = array_except($make, 'password');
        }

        if ($request->question != null) {
            $item = [
                'question' => $request->question,
                'answers' => $request->answers,
                'feedback' => $request->feedback,
            ];
            $make['json'] = serialize(json_encode($item));

            $make = array_except($make, 'question');
            $make = array_except($make, 'answers');
            $make = array_except($make, 'feedback');
        } else {
            $make = array_except($make, 'question');
            $make = array_except($make, 'answers');
            $make = array_except($make, 'feedback');
        }

        $make = array_except($make, '_token');
        $make = array_except($make, '_method');
        $make = array_except($make, 'options');

        if ($make['timeIn'] != null) {
            $make['timeIn'] = Carbon::parse($make['timeIn']);
        }

        if ($make['timeOut'] != null) {
            $make['timeOut'] = Carbon::parse($make['timeOut']);
        }

        $configurates = DB::table('configurates')
            ->where('courses_id', $make['course_id'])
            ->where('contents_id', $id)
            ->get();

        /**
         *  Las actividades y la configuración general queda 100% modificada luego de que se haga la actualización posterior a la creación del curso
         *  El estado borrador se a activado para hacer cambios respectivos antes de ser publicado
         */
        if (count($configurates) > 0) {
            $configurates = DB::table('configurates')
                ->where('courses_id', $make['course_id'])
                ->where('contents_id', $id)
                ->update([
                    'requiredActivities' => $make['assing'],
                ]);
        }

        $make = array_except($make, 'course_id');

        Contents::where('id', $id)->update($make);
        Session::flash('snackbar-success', 'Contenido editado correctamente');

        return ['msj' => 'ok', 'url' => request()->headers->get('referer')];
    }

    public function destroy(Request $request, $id)
    {
        $force = $request->input('force');
        $options = Contents::findOrFail($id);
        if ($force == 1) {
            $options->forceDelete();
            Session::flash(
                'snackbar-warning',
                'El contenido se ha Eliminado totalmente'
            );
            return back();
        }
        $options->delete();
        Session::flash(
            'snackbar-warning',
            'El contenido se ha envíado a la papelera'
        );
        return back();
    }
}
