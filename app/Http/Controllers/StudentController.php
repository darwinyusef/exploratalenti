<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Student;
use App\Models\Selections;
use App\Models\Programs;
use App\Imports\StudentImport;
use App\Http\Controllers\NotificationController as Notify; 

class StudentController extends Controller
{
    public function allRules(string $type = 'CREATED')
    {
        $validations = [
            'phone' => 'required|max_digits:16',
            'phone_attendant' => 'required|max_digits:16',
            'ie' => 'required',
            'register' => 'required',
            Rule::in(['INITIAL', 'REGISTER', 'SELECTION']),
        ];
        if ($type == 'CREATED') {
            $validations['allname'] = 'required';
            $validations['email'] = 'required|email|unique:students';
            $validations['document'] =
                'required|integer|unique:students|max_digits:15';
            $validations['typedocument'] = 'required';
            $send = $validations;
        } elseif ($type == 'EDITED') {
            $validations['email'] = 'required|email';
            $send = $validations;
        }

        return $send;
    }

    public function allMessages()
    {
        return [
            'document.unique' => 'El documento ya existe en la base de datos',
            'document.integer' => 'El documento solo puede ser un número',
            'allname.required' => 'El nombre es requerido',
            'email.required' => 'El email es requerido',
            'email.email' => 'El Email no es valido',
            'email.unique' =>
                'El Email actualmente ya existe en la base de datos',
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $document = $req->input('document');
        $cantidad = $req->input('cantidad');

        if (is_null($cantidad)) {
            $cantidad = 30;
        }

        if (is_null($document)) {
            $users = Student::paginate($cantidad);
        } else {
            $users = [];
        }

        if ($users->currentPage() + 1 > $users->lastPage()) {
            $next = $users->currentPage();
        } else {
            $next = $users->currentPage() + 1;
        }

        if ($users->currentPage() == 1) {
            $previous = $users->currentPage();
        } else {
            $previous = $users->currentPage() - 1;
        }
        return Notify::ms(
            'ok',
            201,
            [
                'users' => $users,
                'next' => $next,
                'previous' => $previous,
                'lastpage' => $users->lastPage(),
                'currentPage' => $users->currentPage(),
            ],
            'Se a listado correctamente'
        );
        // view: home
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        $rules = $this->allRules();
        $customMessage = $this->allMessages();
        $validation = Validator::make($req->all(), $rules, $customMessage);

        //here 422 means unprocessable entity
        if ($validation->fails()) {
            return Notify::ms('error', 400, $validator, 'Validations Error');
        }

        Student::create([
            'allname' => $req->allname,
            'email' => $req->email,
            'uuid' => (string) Str::uuid(),
            'document' => $req->document,
            'typedocument' => $req->typedocument,
            'phone' => $req->phone,
            'phone_attendant' => $req->phone_attendant,
            'ie' => $req->ie,
            'active' => 1,
            'asistencia' => date('Y-m-d H:i:s'),
            'register' => 'CONNECTED',
        ]);

        return Notify::ms('ok', 201, [], 'Student Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $req, string $uuid)
    {
        $students = Student::where('uuid', $uuid)->first();
        return Notify::ms('ok', 201, $students, 'Se a listado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function search_document(Request $req, string $document)
    {
        $students = Student::where('document', $document)->first();
        if ($students != null) {
            return Notify::ms(
                'ok',
                201,
                $students,
                'Student as Search Successfully'
            );
        } else {
            return Notify::ms(
                'error',
                400,
                $students,
                'El estudiante no existe en la base de datos'
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $req, string $uuid)
    {
        $rules = $this->allRules('EDITED');

        // Only validate email
        $onlyEmailValidate = Student::where('uuid', $uuid)->first();

        if ($onlyEmailValidate == null) {
            return Notify::ms(
                'no-found',
                404,
                $validator,
                'Student dont exist in database'
            );
        }

        if ($req->email == $onlyEmailValidate->email) {
            $rules['email'] = 'required';
        } else {
            $rules['email'] = 'required|email|unique:students';
        }
        $customMessage = $this->allMessages();
        $validation = Validator::make($req->all(), $rules, $customMessage);

        //here 422 means unprocessable entity
        if ($validation->fails()) {
            return Notify::ms('error', 400, $validator, 'Validations Error');
        }

        $upStudent = Student::where('uuid', $uuid)->update([
            'email' => $req->email,
            'phone' => $req->phone,
            'phone_attendant' => $req->phone_attendant,
            'ie' => $req->ie,
            'active' => 1,
            'register' => 'CONNECTED',
        ]);

        $upStudentAll = Student::where('uuid', $uuid)->first();
        return Notify::ms(
            'ok',
            201,
            $upStudentAll,
            'Student Update Successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function asistencia(Request $req, string $uuid)
    {
        $rules = [
            'phone' => 'required|max_digits:16',
            // 'phone_attendant' => 'max_digits:16',
            'accept' => 'required|boolean',
        ];

        // Only validate email
        $onlyEmailValidate = Student::where('uuid', $uuid)->first();

        if ($onlyEmailValidate == null) {
            return Notify::ms(
                'no-found',
                404,
                [],
                'Student dont exist in database'
            );
        }

        if ($req->email == $onlyEmailValidate->email) {
            $rules['email'] = 'required';
        } else {
            $rules['email'] = 'required|email|unique:students';
        }
        $customMessage = $this->allMessages();
        $validation = Validator::make($req->all(), $rules, $customMessage);

        //here 422 means unprocessable entity
        if ($validation->fails()) {
            return Notify::ms('error', 400, $validator, 'Validations Error');
        }

        $upStudent = Student::where('uuid', $uuid)->update([
            'email' => $req->email,
            'phone' => $req->phone,
            // 'phone_attendant' => $req->phone_attendant,
            'active' => 1,
            'asistencia' => date('Y-m-d H:i:s'),
            'register' => 'CONNECTED',
        ]);

        $upStudentAll = Student::where('uuid', $uuid)->first();
        return Notify::ms(
            'ok',
            201,
            $upStudentAll,
            'Student Update Successfully'
        );
    }

    public function updateDocument(Request $req, string $uuid)
    {
        $validations = [];
        $validations['allname'] = 'required';
        $validations['email'] = 'required|email';
        $validations['typedocument'] = 'required';
        $validations['document'] = 'required|integer|max_digits:15';

        $onlyDocumentValidate = Student::where('uuid', $uuid)->first();

        if ($onlyDocumentValidate == null) {
            return Notify::ms(
                'no-found',
                404,
                $final,
                'Student dont exist in database'
            );
        }

        if ($req->document == $onlyDocumentValidate->document) {
            $rules['document'] = '';
            $rules['typedocument'] = '';
        } else {
            $rules['typedocument'] = 'required';
            $rules['document'] =
                'required|integer|unique:students|max_digits:15';
        }

        $customMessage = $this->allMessages();
        $validation = Validator::make(
            $req->all(),
            $validations,
            $customMessage
        );

        if ($validation->fails()) {
            return Notify::ms('error', 400, $validator, 'Validations Error');
        }

        $upStudent = Student::where('uuid', $uuid)->update([
            'email' => $req->email,
            'allname' => $req->allname,
            'document' => $req->document,
            'typedocument' => $req->typedocument,
            'modify_document' => date('Y-m-d H:i:s'),
        ]);

        $upStudentAll = Student::where('uuid', $uuid)->first();

        return Notify::ms(
            'ok',
            201,
            $upStudentAll,
            'Student Update Document Successfully'
        );
    }

    public function firstSelected(Request $req, string $uuid)
    {
        $id = Student::select('id')
            ->where('uuid', $uuid)
            ->first();

        $validation = Validator::make(
            $req->all(),
            [
                'programs_a' => 'required|integer',
                'programs_b' => 'integer',
                'fist_option' => 'required|integer',
            ],
            []
        );

        //here 422 means unprocessable entity
        if ($validation->fails()) {
            return Notify::ms('error', 400, $validator, 'Validations Error');
        }

        $selections = Selections::where('students_id', $id->id)->first();

        $pr_a = $this->validateProgram($req->programs_a);
        $pr_b = $this->validateProgram($req->programs_b);

        if ($selections == null) {
            Selections::create([
                'uuid' => (string) Str::uuid(),
                'students_id' => $id->id,
                'programs_a' => $pr_a,
                'fist_option' => $req->fist_option,
                'programs_b' => $pr_b,
                'accept' => date('Y-m-d H:i:s.000000Z'),
                'active' => 1,
            ]);
        } else {
            Selections::where('students_id', $id->id)->update([
                'students_id' => $id->id,
                'programs_a' => $pr_a,
                'fist_option' => $req->fist_option,
                'programs_b' => $pr_b,
                'accept' => date('Y-m-d H:i:s.000000Z'),
                'active' => 1,
            ]);
        }

        $selectionsFinal = Selections::where('students_id', $id->id)->first();
        return Notify::ms(
            'ok',
            201,
            $selectionsFinal,
            'Selection Created Successfully'
        );
    }

    public function reportFinal(Request $req, string $uuid)
    {
        $reportA = null;
        $reportB = null;
        $student = Student::where('uuid', $uuid)->first();

        if (is_null($student)) {
            return Notify::ms(
                'ok',
                201,
                [],
                'El estudiante no se encuentra en la selección'
            );
        }

        $reportValidtion = Selections::select('programs_a', 'programs_b')
            ->where('students_id', $student->id)
            ->first();

        if (!is_null($reportValidtion->programs_a)) {
            $reportA = Selections::where('students_id', $student->id)
                ->leftJoin('programs', 'programs_a', '=', 'programs.id')
                ->first();
        }

        if (!is_null($reportValidtion->programs_b)) {
            $reportB = Selections::where('students_id', $student->id)
                ->leftJoin('programs', 'programs_b', '=', 'programs.id')
                ->first();
        }
        return Notify::ms(
            'ok',
            201,
            [
                'data' => [$reportA, $reportB],
                'student' => $student,
            ],
            'Report Successfully'
        );
    }

    private function validateProgram($program_id)
    {
        $program = Programs::where('id', $program_id)->first();

        if ($program === null) {
            return null;
        }

        return $program->id;
    }

    public function indexPrograms()
    {
        $program = Programs::all();
        return Notify::ms('ok', 201, $program, 'Se a listado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $req, string $uuid)
    {
        Student::where('uuid', $uuid)->delete();
        return Notify::ms('ok', 201, $final, 'Student Delete Successfully');
    }

    public function uploadData(Request $req)
    {
        $el = Excel::import(new StudentImport(), $req->file('file'));
    }
}
