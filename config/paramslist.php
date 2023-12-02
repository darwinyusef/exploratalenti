<?php

use Illuminate\Support\Str;

// Parametros Globales que afectan a toda la aplicación
return [
    'sentry:logs' => true,
    'app:name' => env('APP_URL'),
    'tratamiento' => env('APP_URL'),
    'autoDelete' => env('APP_URL') . '/api/autodelete/%uuid%/?deleteForever=no',
    'verifyEmail' => env('APP_URL') . '/api/validarmail',
    'aprobed:email' => [
        'principal' => 'wsgestor@gmail.com',
        'no:reply' => 'no-reply@aquicreamos.com',
    ],
    'languages' => ['en,es', 'es', 'en', 'pt_BR'],
    'languages:principal' => 'en',
    'status' => [
        'inactivo' => 1,
        'valCodEnviado' => 2,
        'valCodAceptado' => 3,
        'valCodRechazado' => 4,
        'aceptado' => 5,
        'autoRetiro' => 6,
        'rechazado' => 7,
    ],
    // vip: premium, tiempo especifico, pagado, evento -- informacion: mas información, descripción coficada de un programa o libro
    'taxonomy:types' => ['category', 'item', 'tag', 'type', 'information', 'vip'],
    'course:types' => ['normal', 'conference', 'meeting', 'poadcast'],  //'masterClass', 'tutorial', 'webinar', 'seminary', 'smallTalk'
    'content:types' => ['introduccion', 'investigacion', 'audio', 'ticktock-post', 'youtube', 'meet', 'cuestionario_url', 'recurso_url', 'pregunta', 'pregunta_url',  'poadcast', 'presentacion', 'presentacion_url', 'facebook-post', 'twitter-post', 'instagram-post', 'image', 'infographic', 'guia', 'item', 'introduction', 'archivo', 'recurso', 'audio_url', 'contenido', 'cuestionario', 'tarea', 'diapositiva', 'read'], //'video',
    'content:status' => [
        'Abierto' => 3,
        'Bloqueado' => 0,
        'Borrador' => 5,
        'En revision' => 4,
        'Publicado' => 1,
        'Privado' => 2,
    ],
    'post:status' => [],
    //-- no es posible los cambios si desea realizar alguno en el origin debe modificar el contenido general de la siguiente manera 
    // -- En public function show(Request $request, $id){ ContentsController
    /**
     * themplates
     * url|image
     * audio: url|file|text
     * object: url|iframe
     * file|text 
     * image
     */
    'content:types-unad' => [
        ['key' => 'introduction', 'name' => 'Introduccion', 'description' => 'Describa el contenido introductorio del curso', 'icon' => 'fa-file', 'origin' => 'text|iframe|image'],

        ['key' => 'audio', 'name' => 'Audio', 'description' => 'Cargue archivos de Audio (mp3,wav,wma)', 'icon' => 'fa-file-audio-o', 'origin' => 'url|iframe|file|text'],
        ['key' => 'poadcast', 'name' => 'Audio tipo Poadcast URL', 'description' => 'Url ifreme de Poadcast', 'icon' => 'fa-file-audio-o', 'origin' => 'url|iframe|file|text'],
        ['key' => 'audio_url', 'name' => 'Audio URL', 'description' => 'Url ifreme de tipo Audio', 'icon' => 'fa-file-audio-o', 'origin' => 'url|iframe|file|text'],
        ['key' => 'recurso_url', 'name' => 'Mostrar Recurso URL Externo', 'description' => 'Recurso que requiere una url', 'icon' => 'fa-paperclip', 'origin' => 'url|iframe|file|text'],

        ['key' => 'ticktock-post', 'name' => 'Ticktock Post', 'description' => 'Video de Ticktock para incluir como post', 'icon' => 'fa-television', 'origin' => 'url|iframe'],
        ['key' => 'youtube', 'name' => 'Youtube', 'description' => 'Url iframe de Youtube', 'icon' => 'fa-youtube-play', 'origin' => 'url|iframe'],
        // ['key' => 'video', 'name' => 'Video en otras plataformas', 'description' => 'Url ifreme de Otros formatos de video', 'icon' => 'fa-file-movie-o', 'origin' => 'iframe'],
        ['key' => 'presentacion_url', 'name' => 'Presentación de tipo URL', 'description' => 'Url ifreme de tipo Presentación', 'icon' => 'fa-file-powerpoint-o', 'origin' => 'url|iframe'],
        ['key' => 'facebook-post', 'name' => 'Facebook Post', 'description' => 'Facebook para incluir como post', 'icon' => 'fa-facebook-official', 'origin' => 'url|iframe'],
        ['key' => 'twitter-post', 'name' => 'Twitter Post', 'description' => 'Twitter para incluir como post', 'icon' => 'fa-twitter', 'origin' => 'url|iframe'],
        ['key' => 'instagram-post', 'name' => 'Instagram Post', 'description' => 'Instagram para incluir como post', 'icon' => 'fa-instagram', 'origin' => 'url|iframe'],
        ['key' => 'object-post', 'name' => 'Object Post', 'description' => 'Objeto para incluir como post', 'icon' => 'fa-hand-paper-o', 'origin' => 'url|iframe'],
        
        ['key' => 'image', 'name' => 'Imagen Object', 'description' => 'Cargue archivos de Imagen de tipo (jpg,jpeg,gif,png)', 'icon' => 'fa-file-image-o', 'origin' => 'image'],
        ['key' => 'infographic', 'name' => 'Infografia Imagen', 'description' => 'Cargue archivos de Imagen de tipo extralargas (jpg,jpeg,gif,png)', 'icon' => 'fa-file-image-o', 'origin' => 'image'],
        
        ['key' => 'pregunta_url', 'name' => 'Pregunta en URL', 'description' => 'Pregunta para Quizz en URL', 'icon' => 'fa-book', 'origin' => 'url|iframe|question'],
        ['key' => 'cuestionario_url', 'name' => 'Cuestionario URL', 'description' => 'Url ifreme de tipo cuestionario', 'icon' => 'fa-book', 'origin' => 'url|iframe|question'],

        ['key' => 'file', 'name' => 'Archivo', 'description' => 'Cargue archivos de tipo (pdf,xlsx,docs,txt,csv,ai,psd)', 'icon' => 'fa-file-zip-o', 'origin' => 'file|text'],
        ['key' => 'read', 'name' => 'Archivo PDF Lectura', 'description' => 'Cargue archivos de tipo (pdf)', 'icon' => 'fa-file-pdf-o', 'origin' => 'file|text'],
        ['key' => 'tarea', 'name' => 'Evidencia de Tarea', 'description' => 'Tareas de tipo (pdf,xlsx,docs,txt,csv,ai,psd)', 'icon' => 'fa-desktop', 'origin' => 'file|text'],

        ['key' => 'pregunta', 'name' => 'Responde a la pregunta', 'description' => 'Se habilita el contenido para contestar en texto la pregunta', 'icon' => 'fa-book', 'origin' => 'question'],

        ['key' => 'meet', 'name' => 'Reunion', 'description' => 'Reunión usando plataformas de meeting', 'icon' => 'fa-group', 'origin' => 'url|image'],
    ],

    'content:activity' => ['Tarea', 'Actividad', 'Video', 'Parcial', 'Material', 'Notificacion'], // campo en BD
    'content:timeline' => ['horas', 'minutos', 'dias', 'semanas', 'meses', 'anios'], // campo en BD
    'content:aula' => ['Clase online','Masterclass','Video Tutorial', 'Audio Tutorial', 'Salon', 'Auditorio'], 
    'content:classroom' => ['del Contenido', 'la Actividad Online', 'la Actividad Inmersiva', 'la Conferencia', 'de la actividad del  Campamento de Codigo (CodeCamp)', 'del Marketplace', 'del Salon Físico', 'del Auditorio', 'del Poadcast', 'de la Aula TEA', 'del Saas(Software as Service)', 'del Meet', 'de nuestro canal de Discord'],     
    'type:notification' => ['todos', 'email', 'mensaje', 'push'],

    'post:types' => ['attachment', 'page', 'post', 'revision', 'portfolio', 'product', 'directory', 'publicity', 'course', 'homework', 'reading', 'leader', 'poadcast', 'video'],
    'interaction:types' => ['notas', 'preguntas', 'calificaciones', 'respuesta', 'ver:archivo', 'ver:video', 'ver:leer', 'ver:comunicar', 'ver:reunion', 'descargar', 'leer', 'contenido'],
    'course:state' => ['published', 'draft', 'pending review'],
    'btn:colors' => ['bg-red', 'bg-yellow', 'bg-light-blue', 'bg-green'],
    'disk:files' => [
        'admins' => true,
        'images' => true,
        'public' => true,
        'courses' => false,
        'users' => true
    ],
    'content:types.elements' => [
        'investigacion' => false,
        'pregunta_url' => 'url',
        'video' => 'video',
        'youtube' => 'youtube',
        'audio' => 'audio',
        'audio_url' => 'url',
        'poadcast' => 'audio',
        'presentacion' => false,
        'presentacion_url' => 'url',
        'ticktock-post' => 'url',
        'facebook-post' => 'url',
        'twitter-post' => 'url',
        'instagram-post' => 'url',
        'image' => 'image',
        'infographic' => 'image',
        'guia' => false,
        'item' => false,
        'introduction' => true,
        'archivo' => 'url',
        'recurso' => 'url',
        'contenido' => false, // item que construira la guia | presentacion según el padre
        'cuestionario' => 'url',
        'tarea' => false, // contenido que genera un input para cargar un archivo
        'diapositiva' => 'url',
    ],
    'initial:user' => [
        'lang' => 'es',
        'theme' => 'ligth',
        'color_theme' => 'ligth-blue',
    ],
    'slides' => [
        'slideTitle' => true,
        'slideContent' => true,
        'slideEndPresentation' => true,
        'slide2Objects' => true,
        'default' => true
    ],
    'metas' => [
        'meta:tags' => true,
        'analitics' => true,
        'google:analiticsCode' => '',
        'construct:email' => true,
        'audience:facebook' => true,
        'audience:twitter' => true,
        'image:optimization' => [
            'twitter-xs' => '120x120',
            'twitter-sm' => '280x150',
            'twitter-md' => '1080x1080',
            'instagram-md' => '1080x1080',
            'instagram-st' => '1080x1920',
            'facebook-md' => '1080x1080',
            'facebok-st' => '1080x1920',
            'facebok-web' => '1920x1080',
        ]
    ],
];