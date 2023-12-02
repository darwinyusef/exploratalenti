<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use App\Models\Contents;
use Illuminate\Http\Request;
use App\Models\Taxonomies;
use App\Http\Controllers\StateController;
use App\Http\Controllers\NotificationController as Notify; 
use Auth, Session, DB, Carbon\Carbon;

class InteractionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:interaction.update'])->only([
            'update',
            'edit',
        ]);
        $this->middleware(['permission:interaction.create'])->only([
            'create',
            'store',
        ]);
        $this->middleware(['permission:interaction.list'])->only(['index']);
        $this->middleware(['permission:interaction.destroy'])->only([
            'destroy',
        ]);
        
    }

    public function valArray($type)
    {
        $array = [
            'ver:todo',
            'notas',
            'seleccion:multiple',
            'calificaciones',
            'respuesta:text',
            'cargar:archivo',
            'cargar:imagen',
            'escuchar:audio',
            'ver:video',
            'leer',
            'close',
            'asistir:reunion',
            'descargar',
        ];
        if (in_array($type, $array)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function initialInteraction(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $make = $request->all();
            $revision = DB::table('configurates')
                ->where('courses_id', $make['course_id'])
                ->where('contents_id', $make['content_id'])
                ->where('user_id', Auth::user()->id);
            if ($request->interactions_id != null) {
                $revision = $revision
                    ->where('interactions_id', $request->interactions_id)
                    ->get();
            } else {
                $revision = $revision->get();
            }
            if (count($revision) == 0) {
                $interactionNew = Interaction::create([
                    'interaction' => $request->type,
                    'slug' => str_slug(1 . ' ' . $request->type, '-'),
                ]);
                $content = Contents::find($make['content_id']);
                Interaction::where('id', $interactionNew->id)->update([
                    'interaction' => $interactionNew->id . '-' . $content->slug,
                    'slug' => str_slug(
                        $interactionNew->id .
                            '-' .
                            $content->slug .
                            '-' .
                            $request->type,
                        '-'
                    ),
                    'status' => 1,
                    'type' => $request->type,
                ]);
                DB::table('configurates')->insert([
                    'keys' => 'activity',
                    'courses_id' => $make['course_id'],
                    'contents_id' => $make['content_id'],
                    'interactions_id' => $interactionNew->id,
                    'user_id' => Auth::user()->id,
                    'status' => 1,
                    'requiredActivities' =>
                        $make['assing'] == null
                            ? null
                            : 'close|' . $make['assing'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                return ['msj' => 'ok', 'id' => $interactionNew->id];
            }

            // se crea el state
            $state = [
                'key' => 'initialInteraction',
                'description' => 'Apertura inicial de contenido',
                'status' => 1,
                'stateable' => $make['content_id'],
                'user_id' => Auth::user()->id,
            ];

            StateController::state_store($state);

            return ['msj' => 'ok', 'id' => $revision[0]->interactions_id];
        }
    }

    public function interactionQuestion(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $make = $request->all();
            if ($make['response']) {
                Interaction::where('id', $make['interaction_id'])->update([
                    'response' => $make['response'],
                    'status' => 1,
                    'type' => $request->type,
                ]);
                $configurates = DB::table('configurates')
                    ->where('interactions_id', $make['interaction_id'])
                    ->where('courses_id', $make['course_id'])
                    ->where('contents_id', $make['content_id'])
                    ->get();

                $selection = [];
                if ($configurates[0]->userActivity != null) {
                    $selection = explode('|', $configurates[0]->userActivity);

                    if (!in_array('question', $selection)) {
                        array_push($selection, 'question');
                    }
                } else {
                    $selection = ['question'];
                }
                $selection = implode('|', $selection);

                // se crea el state
                $state = [
                    'key' => 'interactionQuestion',
                    'description' =>
                        'Respuesta a pregunta de la actividad ' .
                        $configurates[0]->id,
                    'status' => 1,
                    'stateable' => $configurates[0]->id,
                    'user_id' => Auth::user()->id,
                ];

                StateController::state_store($state);

                DB::table('configurates')
                    ->where('id', $configurates[0]->id)
                    ->update([
                        'userActivity' => $selection,
                        'updated_at' => Carbon::now(),
                    ]);
            } else {
                abort(406);
            }
            return ['msj' => 'ok'];
        } else {
            abort(409);
        }
    }

    public function interactionChargeFile(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $make = $request->all();
            $configurates = DB::table('configurates')
                ->where('interactions_id', $make['interaction_id'])
                ->where('courses_id', $make['course_id'])
                ->where('contents_id', $make['content_id'])
                ->get();

            $selection = [];
            if ($configurates[0]->userActivity != null) {
                $selection = explode('|', $configurates[0]->userActivity);

                if ($request->type == 'cargar:archivo') {
                    if (!in_array('file', $selection)) {
                        array_push($selection, 'file');
                    }
                }

                if ($request->type == 'cargar:imagen') {
                    if (!in_array('image', $selection)) {
                        array_push($selection, 'image');
                    }
                }
            } else {
                if ($request->type == 'cargar:archivo') {
                    $selection = ['file'];
                }
                if ($request->type == 'cargar:imagen') {
                    $selection = ['image'];
                }
            }
            $selection = implode('|', $selection);

            // se crea el state
            $state = [
                'key' => 'interactionChargeFile',
                'description' =>
                    'Respuesta a pregunta de la actividad ' .
                    $configurates[0]->id,
                'status' => 1,
                'stateable' => $configurates[0]->id,
                'user_id' => Auth::user()->id,
            ];

            StateController::state_store($state);

            DB::table('configurates')
                ->where('id', $configurates[0]->id)
                ->update([
                    'userActivity' => $selection,
                    'updated_at' => Carbon::now(),
                ]);
        }
    }

    public function interactionQuestionMultiple(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $make = $request->all();

            if ($make['response'] && $make['value']) {
                $position = strripos($make['value'], ':');
                $cleanString = substr($make['value'], 0, $position);
                $selectionQuestion = explode(';', $cleanString);
                $val = substr($make['value'], $position + 1, $position + 3);
                $trueResponse = $selectionQuestion[$val - 1];

                if ($make['response'] == rtrim(ltrim($trueResponse))) {
                    $value = 1;
                } else {
                    $value = 0;
                }

                Interaction::where('id', $make['interaction_id'])->update([
                    'response' => $make['response'],
                    'value' => $value,
                    'status' => 1,
                    'type' => $request->type,
                ]);

                $configurates = DB::table('configurates')
                    ->where('interactions_id', $make['interaction_id'])
                    ->where('courses_id', $make['course_id'])
                    ->where('contents_id', $make['content_id'])
                    ->get();

                $selection = [];
                if ($configurates[0]->userActivity != null) {
                    $selection = explode('|', $configurates[0]->userActivity);

                    if (!in_array('question', $selection)) {
                        array_push($selection, 'question');
                    }
                } else {
                    $selection = ['question'];
                }

                $selection = implode('|', $selection);

                // se crea el state
                $state = [
                    'key' => 'interactionQuestionMultiple',
                    'description' =>
                        'Respuesta a pregunta multiple en la actividad ' .
                        $configurates[0]->id,
                    'status' => 1,
                    'stateable' => $configurates[0]->id,
                    'user_id' => Auth::user()->id,
                ];

                StateController::state_store($state);

                DB::table('configurates')
                    ->where('id', $configurates[0]->id)
                    ->update([
                        'userActivity' => $selection,
                        'score' => $value,
                        'updated_at' => Carbon::now(),
                    ]);
            } else {
                abort(406);
            }
            return ['msj' => 'ok'];
        } else {
            abort(409);
        }
    }

    public function interactionText(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $make = $request->all();
            if ($make['content']) {
                Interaction::where('id', $make['interaction_id'])->update([
                    'content' => $make['content'],
                    'status' => 1,
                    'type' => $request->type,
                ]);
                $configurates = DB::table('configurates')
                    ->where('interactions_id', $make['interaction_id'])
                    ->where('courses_id', $make['course_id'])
                    ->where('contents_id', $make['content_id'])
                    ->get();

                $selection = [];
                if ($configurates[0]->userActivity != null) {
                    $selection = explode('|', $configurates[0]->userActivity);

                    if (!in_array('text', $selection)) {
                        array_push($selection, 'text');
                    }
                } else {
                    $selection = ['text'];
                }
                $selection = implode('|', $selection);

                // se crea el state
                $state = [
                    'key' => 'interactionText',
                    'description' =>
                        'Vinculación de información a la actividad ' .
                        $configurates[0]->id,
                    'status' => 1,
                    'stateable' => $configurates[0]->id,
                    'user_id' => Auth::user()->id,
                ];

                StateController::state_store($state);

                DB::table('configurates')
                    ->where('id', $configurates[0]->id)
                    ->update([
                        'userActivity' => $selection,
                        'updated_at' => Carbon::now(),
                    ]);
            } else {
                abort(406);
            }
            return ['msj' => 'ok'];
        } else {
            abort(409);
        }
    }

    public function closeInteraction(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $make = $request->all();

            Interaction::where('id', $make['interaction_id'])->update([
                'status' => 3,
                'type' => $request->type,
            ]);

            $configurates = DB::table('configurates')
                ->where('interactions_id', $make['interaction_id'])
                ->where('courses_id', $make['course_id'])
                ->where('contents_id', $make['content_id'])
                ->where('user_id', Auth::user()->id)
                ->get();

            $selection = [];
            if ($configurates[0]->userActivity != null) {
                $selection = explode('|', $configurates[0]->userActivity);

                if (!in_array('close', $selection)) {
                    array_push($selection, 'close');
                }
            } else {
                $selection = ['close'];
            }
            $selection = implode('|', $selection);

            $configurates = DB::table('configurates')
                ->where('interactions_id', $make['interaction_id'])
                ->where('courses_id', $make['course_id'])
                ->where('contents_id', $make['content_id'])
                ->where('user_id', Auth::user()->id)
                ->update([
                    'userActivity' => $selection,
                    'updated_at' => Carbon::now(),
                    'closed_at' => Carbon::now(),
                ]);

            // se crea el state
            $state = [
                'key' => 'closeInteraction',
                'description' =>
                    'Cierre de la actividad ' . $make['content_id'],
                'status' => 1,
                'stateable' => $make['content_id'],
                'user_id' => Auth::user()->id,
            ];

            StateController::state_store($state);

            return ['msj' => 'ok', 'url' => request()->headers->get('referer')];
        } else {
            abort(409);
        }
    }

    public function interactionFileAudio(Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $make = $request->all();
            if ($make['response']) {
                Interaction::where('id', $make['interaction_id'])->update([
                    'response' => $make['response'],
                    'type' => $make['type'],
                ]);
                $configurates = DB::table('configurates')
                    ->where('interactions_id', $make['interaction_id'])
                    ->where('courses_id', $make['course_id'])
                    ->where('contents_id', $make['content_id'])
                    ->get();

                $selection = [];
                if ($configurates[0]->userActivity != null) {
                    $selection = explode('|', $configurates[0]->userActivity);

                    if (!in_array('audio', $selection)) {
                        array_push($selection, 'audio');
                    }
                } else {
                    $selection = ['audio'];
                }
                $selection = implode('|', $selection);

                DB::table('configurates')
                    ->where('id', $configurates[0]->id)
                    ->update([
                        'userActivity' => $selection,
                        'updated_at' => Carbon::now(),
                    ]);
            } else {
                abort(406);
            }
            return ['msj' => 'ok'];
        } else {
            abort(409);
        }
    }

    public function store(Request $request)
    {
        $make = $request->all();

        $make = array_except($make, '_token');
        $make = array_add($make, 'user_id', Auth::user()->id);

        Interaction::create($make);
        Session::flash('snackbar-success', 'Contenido creado correctamente');
        return back();
    }

    public function add_img()
    {
        $interaction = Interaction::find(1)
            ->files()
            ->create(['files_id' => 1]);
        Session::flash('snackbar-success', 'Contenido creado correctamente');
        return $interaction;
    }

    public function edit($id, Request $request)
    {
        if ($this->valArray($request->type) == 1) {
            $type = $request->type;
        }
        $taxonomies = Taxonomies::all();
        $interactions = Interaction::where('id', $id)
            ->where('type', $type)
            ->first();
        // view: backend.interaction.update
        return Notify::ms(
            'ok',
            201,
            [$type, $interactions, $taxonomies],
            'Se a listado correctamente'
        );
    }

    public function update(Request $request, $id)
    {
        $make = $request->all();
        if ($make['type_list'] == 'draft') {
            $make['password'] = $make['password'];
        } else {
            $make['password'] = null;
        }
        $make = array_except($make, '_method');
        $make = array_except($make, '_token');
        $make = array_except($make, 'type_list');
        $make = array_add($make, 'user_id', Auth::user()->id);

        $int = Interaction::where('id', $id)->update($make);
        Session::flash('snackbar-success', 'Contenido editado correctamente');
        // back 
        return Notify::ms('ok', 201, $int, 'Contenido editado correctamente');
    }

    public function destroy(Request $request, $id)
    {
        $force = $request->input('force');
        $options = Interaction::findOrFail($id);
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
