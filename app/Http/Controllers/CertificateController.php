<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Courses;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\State;
use App\Models\DataUser as Data;
use App\Models\User;
use Auth, Validator, Redirect, Session;
use Carbon\Carbon, QrCode, DB;
use App\Http\Controllers\NotificationController as Notify; 

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
        return Notify::ms('ok', 201, $totales, 'Certificado creado');
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
        return Notify::ms('ok', 201, $totales, 'Certificado seleccionado');
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
        return Notify::ms('ok', 201, $userCourses, 'Register Certificate');
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
            return Notify::ms(
                'error',
                400,
                $certificates,
                'Certificados por compañía'
            );
        }
        return Notify::ms(
            'ok',
            201,
            $userCourses,
            'No existen actualmente certificados'
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
            return Notify::ms(
                'error',
                400,
                [],
                'Se requiere del número de Identificación para el cargue del Certificado'
            );
        }
        if ($data->firstname == null) {
            return Notify::ms(
                'error',
                400,
                [],
                'Se requiere del número de Primer Nombre para el cargue del Certificado'
            );
        }
        if ($data->lastname == null) {
            return Notify::ms(
                'error',
                400,
                [],
                'Se requiere del número de Apellido para el cargue del Certificado'
            );
        }

        $course = Courses::select('id', 'json')->find($data->idcourse);
        if ($course == null) {
            return Notify::ms(
                'error',
                400,
                [],
                'Error en la selección del curso del usuario: por defina el curso del usuario'
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
            return Notify::ms(
                'error',
                400,
                [],
                'Error en la configuracion del certificado'
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
            return Notify::ms(
                'error',
                400,
                [],
                'Se requiere del número de Identificación para el cargue del Certificado'
            );
        }
        if ($data->firstname == null) {
            return Notify::ms(
                'error',
                400,
                [],
                'Se requiere del número de Primer Nombre para el cargue del Certificado'
            );
        }
        if ($data->lastname == null) {
            return Notify::ms(
                'error',
                400,
                [],
                'Se requiere del número de Apellido para el cargue del Certificado'
            );
        }
        $course = Courses::select('id', 'json')->find($data->idcourse);
        if ($course == null) {
            return Notify::ms(
                'error',
                400,
                [],
                'Error en la selección del curso del usuario: por defina el curso del usuario'
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
            return Notify::ms(
                'error',
                400,
                [],
                'Error en la configuracion del certificado'
            );
        }

        return Notify::ms('ok', 201, $data, 'Visualización de datos');
    }

    public function certificate_destroy(Request $request, $id)
    {
        $force = $request->input('force');
        $group = Certificate::findOrFail($id);

        if ($force == 1) {
            $group->forceDelete();
            return Notify::ms('ok', 201, $data, 'Se a Elimninado el Registro');
        }
        $group->delete();
        Session::flash('snackbar-warning', '');
        return Notify::ms('ok', 201, $data, 'Se a Elimninado el Registro');
    }

    public function certificado_search()
    {
        return Notify::ms('ok', 201, [], 'Información certificado search');
    }

    public function certificado_search_result(Request $request)
    {
        $certificates = Certificate::where('cedula', $request->cedula)->get();
        if (count($certificates) == 0) {
            return Notify::ms('no-found', 404, $validator, 'No existe el certificado');
        }
        return Notify::ms('ok', 201,  $certificates, 'Se a encontrado el Registro');
    }
}
