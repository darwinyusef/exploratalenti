<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Courses;
use App\Entities\Certificate;
use App\Entities\Company;
use App\Entities\State;
use App\Entities\DataUser as Data;
use App\Entities\User;
use Auth, Validator, Redirect, Session;
use Carbon\Carbon, QrCode, DB;

class CertificateController extends Controller
{
    public function image_certificate(Request $request)
    {
        $totales = Courses::select(
            'id',
            'json',
            'status',
            'course',
            'updated_at'
        )->get();
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Certificado creado',
                'data' => $totales,
            ],
            201
        );
    }

    public function image_certificate_store(Request $request)
    {
        $totales = Courses::select(
            'id',
            'json',
            'status',
            'course',
            'updated_at'
        )->get();
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Certificado seleccionado',
                'data' => $totales,
            ],
            201
        );
    }

    public function register_certificate($id)
    {
        $userCourses = DB::table('course_user')
            ->where('user_id', $id)
            ->where('course_user.status', 1)
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

        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Register Certificate',
                'data' => $userCourses,
            ],
            201
        );
    }

    public function certificate_company()
    {
        if (Auth::user()->hasRole('admin')) {
            $certificate_company = Certificate::all();
            foreach ($certificate_company as $certificate) {
                $certificates[] = $certificate;
            }
        } else {
            $company = Company::where(
                'id',
                Auth::user()
                    ->datauser()
                    ->first()->company_id
            )
                ->orWhere(
                    'parent',
                    Auth::user()
                        ->datauser()
                        ->first()->company_id
                )
                ->select('id', 'parent', 'company')
                ->get();
            foreach ($company as $val) {
                $certificate_company = Certificate::where(
                    'company_id',
                    $val->id
                )->get();
                foreach ($certificate_company as $certificate) {
                    $certificates[] = $certificate;
                }
            }
        }
        if (!isset($certificates)) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' => 'Certificados por compañía',
                    'error' => $certificates,
                ],
                400
            );
        }
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'No existen actualmente certificados',
                'data' => $userCourses,
            ],
            201
        );
    }

    public function certificate_store($request)
    {
        $data = $request->toArray();

        $validator = Validator::make($data, Certificate::$rules);

        if ($validator->fails()) {
            //return $validator->errors()->toJson();
            return [0, $validator->errors()->toJson()];
        } else {
            if ($data['certificate'] == 1) {
                // se carga la solicitud
                $lista = Certificate::create($data);
                $serlialize = serialize(
                    json_encode([
                        'view' =>
                            '/view/certificates/' . dump($lista->uuid_text),
                        'pdf' =>
                            '/download/certificates/' . dump($lista->uuid_text),
                    ])
                );
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

    public function certificado_download_pdf($id)
    {
        $data = Certificate::findOrFail($id);
        if ($data->cedula == null) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' =>
                        'Se requiere del número de Identificación para el cargue del Certificado',
                    'error' => [],
                ],
                400
            );
        }
        if ($data->firstname == null) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' =>
                        'Se requiere del número de Primer Nombre para el cargue del Certificado',
                    'error' => [],
                ],
                400
            );
        }
        if ($data->lastname == null) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' =>
                        'Se requiere del número de Apellido para el cargue del Certificado',
                    'error' => [],
                ],
                400
            );
        }

        $course = Courses::select('id', 'json')->find($data->idcourse);
        if ($course == null) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' =>
                        'Error en la selección del curso del usuario: por defina el curso del usuario',
                    'error' => [],
                ],
                400
            );
        }

        $data['certificate'] = json_decode(
            unserialize($course->json)
        )->image_certificate;

        $data['qrcode'] = base64_encode(
            QrCode::format('svg')
                ->size(200)
                ->errorCorrection('H')
                ->generate(env('APP_URL') . '/download/certificates/' . $id)
        );

        if (!str_contains($data['certificate'], 'http')) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' => 'Error en la configuracion del certificado',
                    'error' => [],
                ],
                400
            );
        }

        \PDF::setOptions([
            'dpi' => 90,
            'defaultFont' => 'Helvetica',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);
        $pdf = \PDF::loadView(
            'backend.certificates.files.pdf_certificated',
            $data
        )->setPaper('a4', 'landscape');

        return $pdf->download($id . '.pdf');
    }

    public function certificado_view($id)
    {
        $data = Certificate::findOrFail($id);
        if ($data->cedula == null) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' =>
                        'Se requiere del número de Identificación para el cargue del Certificado',
                    'error' => [],
                ],
                400
            );
        }
        if ($data->firstname == null) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' =>
                        'Se requiere del número de Primer Nombre para el cargue del Certificado',
                    'error' => [],
                ],
                400
            );
        }
        if ($data->lastname == null) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' =>
                        'Se requiere del número de Apellido para el cargue del Certificado',
                    'error' => [],
                ],
                400
            );
        }
        $course = Courses::select('id', 'json')->find($data->idcourse);
        if ($course == null) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' =>
                        'Error en la selección del curso del usuario: por defina el curso del usuario',
                    'error' => [],
                ],
                400
            );
        }

        $data['certificate'] = json_decode(
            unserialize($course->json)
        )->image_certificate;

        $data['url_qrcode'] = env('APP_URL') . '/download/certificates/' . $id;
        $data['qrcode'] = base64_encode(
            QrCode::format('svg')
                ->size(200)
                ->errorCorrection('H')
                ->generate(env('APP_URL') . '/download/certificates/' . $id)
        );

        if (!str_contains($data['certificate'], 'http')) {
            return response()->json(
                [
                    'type' => 'error',
                    'message' => 'Error en la configuracion del certificado',
                    'error' => [],
                ],
                400
            );
        }

        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Visualización de datos',
                'data' => $data,
            ],
            201
        );
    }

    public function certificate_destroy(Request $request, $id)
    {
        $force = $request->input('force');
        $group = Certificate::findOrFail($id);

        if ($force == 1) {
            $group->forceDelete();
            return response()->json(
                [
                    'type' => 'ok',
                    'message' => 'Se a Elimninado el Registro',
                    'error' => [],
                ],
                201
            );
        }
        $group->delete();
        Session::flash('snackbar-warning', '');
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Se a Elimninado el Registro',
                'error' => [],
            ],
            201
        );
    }

    public function certificado_search()
    {
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Información certificado search',
                'error' => [],
            ],
            201
        );
    }

    public function certificado_search_result(Request $request)
    {
        $certificates = Certificate::where('cedula', $request->cedula)->get();
        if (count($certificates) == 0) {
            return abort(404);
        }
        return response()->json(
            [
                'type' => 'ok',
                'message' => 'Se a encontrado el Registro',
                'error' => $certificates,
            ],
            201
        );
    }
}
