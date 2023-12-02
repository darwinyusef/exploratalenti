<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\IndexPageController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api'], function () {
    /// ------> Se establece  el estudiante
    Route::get('search/{document}', [
        StudentController::class,
        'search_document',
    ]);
    Route::get('students', [StudentController::class, 'index']);
    Route::get('students/{uuid}', [StudentController::class, 'show']);
    Route::post('students', [StudentController::class, 'store']);
    Route::put('students/{uuid}/edit', [
        StudentController::class,
        'update',
    ])->name('students.update');
    Route::put('students/{uuid}/asistencia', [
        StudentController::class,
        'asistencia',
    ])->name('students.asistencia');
    Route::put('document/{uuid}', [StudentController::class, 'updateDocument']);
    Route::delete('students/{uuid}', [StudentController::class, 'destroy']);

    /// ------> Seleccción de programas
    Route::post('selection/{uuid}', [
        StudentController::class,
        'firstSelected',
    ]);
    Route::get('programs', [StudentController::class, 'indexPrograms']);
    Route::get('reporte/{uuid}', [StudentController::class, 'reportFinal']);
    Route::post('/el', [StudentController::class, 'uploadData']);

    // Nuevo Sitio
    Route::get('/', [IndexPageController::class, 'index']);
});

Route::get('mailer', function () {
    // $data = [
    //   'card_id' => '14297510',
    //   'name' => 'Yusef Gonzalez',
    //   'email' => 'wsgestor@gmail.com',
    //   'password' => 'Azb3defghe',
    //   'url' => env('APP_URL') . '/login',
    // ];

    $data = [
        'email' => 'cristianismodigitalac@gmail.com',
        'name' => 'darwinyusef',
    ];
    $fecha = Carbon\Carbon::now();
    //validateUser
    \Mail::send('backend.mails.welcome', $data, function ($message) use (
        $data
    ) {
        $message->subject('Activacion de Usuario - Notification');
        $message->from('no-reply@inapayudaspedagogicas.com.co');
        $message
            ->to($data['email'])
            ->cc('laboratorioclinicodiagnosticar@hotmail.com');
    });

    if (count(Mail::failures()) > 0) {
        Log::error(
            'El Email Activacion: ' .
                $data['email'] .
                ' del Usuario: ' .
                $data['name'] .
                'Contiene un error, Email registrado el ' .
                $fecha
        );
    } else {
        Log::info(
            'El Email Activacion: ' .
                $data['email'] .
                ' del Usuario: ' .
                $data['name'] .
                'Fue enviado Correctamente Email registrado el ' .
                $fecha
        );
    }
});


Route::get('/home', function () {
    return \Redirect::action('HomeController@index');
});

Route::get('/registro', 'AuthController@reg_create');
Route::post('/registro', 'AuthController@reg_store');
Route::get('verifyemail', 'AuthController@acceptedEmail');
Route::get('/sendValidator/{id}', 'HomeController@sendValidator');

Route::get('company/certificates', 'CertificateController@certificate_company');
Route::get(
    'download/certificates/{id}',
    'CertificateController@certificado_download_pdf'
);
Route::get('view/certificates/{id}', 'CertificateController@certificado_view');

// Route::get('modify', 'LaboratoriesController@modify_id');

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('route:cache');
    return 'done'; //Return anything
});

Route::get('/activate', function () {
    // view: auth.activate
    return Notify::ms('ok', 201, [], 'Se a activado correctamente');
});


Route::get('/page/{pages}', 'IndexPageController@page');
Route::get('/noticias/{pages}', 'IndexPageController@post');
Route::get('/portafolio/{post}', 'IndexPageController@portfolio');
Route::get('/contacto', 'IndexPageController@contact');
Route::post('/contacto', 'IndexPageController@contact_store')->name('contact');
Route::get('api/company', 'CompanyController@company_api');

Route::prefix('educativo')->group(function () {
    Route::get('/page/{pages}', 'IndexPageController@page');
});

Route::prefix('backend')->group(function () {
    Route::get('/', function () {
        return \Redirect::action('HomeController@index');
    });
    Route::get('home', 'HomeController@index')->name('home');
    Route::resource('usuarios', 'UserAllController', [
        'names' => ['store' => 'user.create'],
    ]);
    Route::resource('empresa', 'CompanyController', [
        'names' => [
            'store' => 'company.store',
            'update' => 'company.update',
            'destroy' => 'company.destroy',
        ],
    ]);
    Route::get('password', 'UserAllController@pass');
    Route::post('password', 'UserAllController@pass_store')->name('pass.store');
    Route::get('password/restaurar', 'UserAllController@restaurar');
    Route::get('theme', 'UserAllController@changeTheme');

    Route::resource('post', 'PostController', [
        'names' => ['store' => 'post.store', 'update' => 'post.update'],
    ]);
    Route::resource('opciones', 'OptionsController', [
        'names' => ['store' => 'option.store', 'update' => 'option.update'],
    ]);
    // Files | Multimedia

    Route::resource('menu', 'LinksController', [
        'names' => [
            'store' => 'menu.store',
            'update' => 'menu.update',
            'destroy' => 'menu.destroy',
        ],
    ]);
    Route::resource('taxonomias', 'TaxonomiesController', [
        'names' => [
            'store' => 'taxonomy.store',
            'update' => 'taxonomy.update',
            'destroy' => 'taxonomy.destroy',
        ],
    ]);
    Route::get('taxonoable', 'TaxonomiesController@taxonoable');
    Route::resource('laboratorios', 'LaboratoriesController', [
        'names' => [
            'store' => 'lab.store',
            'update' => 'lab.update',
            'destroy' => 'lab.destroy',
        ],
    ]);

    // Curso
    Route::resource('curso', 'CoursesController', [
        'names' => [
            'index' => 'course.index',
            'store' => 'course.store',
            'update' => 'course.update',
        ],
    ]);
    Route::get('curso/usuarios/listar', 'CoursesController@courseList');
    Route::get('curso/usuarios/matriculados', 'CoursesController@matriculados');
    Route::post('curso/registro/nuevo', 'CoursesController@register');
    Route::get('curso/validacion/{id}', 'CoursesController@validateCourse');
    Route::delete(
        'curso/validacion/delete/{id}',
        'CoursesController@validateCourseDelete'
    );
    Route::post('curso/ratting/modify/{id}', 'CoursesController@rattingCourse');
    Route::post('curso/view/{id}', 'CoursesController@viewsCourse');
    // Contenido
    Route::resource('contenido', 'ContentsController', [
        'names' => [
            'index' => 'content.index',
            'store' => 'content.store',
            'update' => 'content.update',
        ],
    ]);
    Route::get('contenido/lista/{id}', 'ContentsController@lista');
    // Actividad
    Route::resource('actividad', 'InteractionController', [
        'names' => [
            'store' => 'interaction.store',
            'update' => 'interaction.update',
        ],
    ]);
    Route::post(
        'actividad/inicial/interaccion',
        'InteractionController@initialInteraction'
    );
    // Generar interacciones
    Route::put('interactiontext', 'InteractionController@interactiontext');
    Route::put(
        'interactionChargeFile',
        'InteractionController@interactionChargeFile'
    );
    Route::put(
        'interactionQuestionMultiple',
        'InteractionController@interactionQuestionMultiple'
    );
    Route::put(
        'interactionQuestion',
        'InteractionController@interactionQuestion'
    );
    Route::put(
        'interactionFileAudio',
        'InteractionController@interactionFileAudio'
    );
    Route::put('closeInteraction', 'InteractionController@closeInteraction');
    // Notas
    Route::get('notas/{id}', 'NotasController@notasUser');
    Route::post('notas', 'NotasController@notasUserStore');
    Route::get('notas/certificacion/{id}', 'NotasController@certificacion');

    // Estado
    Route::get('state', 'StateController@state_show');
    Route::post('state/store', 'StateController@state_store');
    Route::put('state/{id}', 'StateController@state_update');
    Route::delete('state/{id}', 'StateController@state_delete');
    Route::get('state/{id}/edit', 'StateController@state_edit');
    Route::get('/report/users/company', 'StateController@reportCompanyUser');

    // Certificado
    Route::get('certificado/image', 'CertificateController@image_certificate');
    Route::delete(
        'certificado/{id}',
        'CertificateController@certificate_destroy'
    );

    Route::get(
        'search/certificates/',
        'CertificateController@certificado_search'
    );
    Route::get(
        'search/certificates/result',
        'CertificateController@certificado_search_result'
    );
    Route::get(
        'certificado/curso/{id}',
        'CertificateController@register_certificate'
    );

    /* Route::get('moodle/cargar', 'LaboratoriesController@chargue_moodle');
     Route::get('moodle/listado/duplicado', 'LaboratoriesController@list_mail_moodle');*/

    //ROLES y PERMISOS
    Route::prefix('roles')->group(function () {
        Route::get('list', 'RolesController@roles');
        Route::get('list/{id}', 'RolesController@roles_store')->name(
            'admin.store.roles'
        );
        Route::get('permisos', 'RolesController@permission')->name(
            'admin.permission'
        );
        Route::post('permisos', 'RolesController@permission_store')->name(
            'admin.permission.store'
        );
        Route::post('asignar', 'RolesController@assign')->name(
            'admin.assign.store'
        );
    });

    //FILES
    Route::get('fileable', 'MultimediaController@fileable');
    Route::get('fileable/destroy', 'MultimediaController@fileable_destroy');
    Route::resource('archivos', 'MultimediaController', [
        'names' => [
            'store' => 'file.store',
            'destroy' => 'file.destroy',
            'update' => 'file.update',
            'edit' => 'file.edit',
        ],
    ]);
});
