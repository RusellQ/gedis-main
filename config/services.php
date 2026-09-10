<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sat' => [
        'signing_endpoint' => env('SAT_SIGNING_ENDPOINT', ''),
        'signing_api_key' => env('SAT_SIGNING_API_KEY', ''),
        'cert_path' => env('SAT_SIGNING_CERT_PATH', ''),
    ],

    'oficio' => [
        'leyenda_anio_default' => env('OFICIO_LEYENDA_ANIO', ''),
        'lugar' => env('OFICIO_LUGAR', 'Chetumal, Quintana Roo'),
        'firma_nombre' => env('OFICIO_FIRMA_NOMBRE', 'Licdo. Gabriel Caamal Pérez'),
        'firma_cargo' => env('OFICIO_FIRMA_CARGO', 'Director General del Archivo General del Estado de Quintana Roo'),
    ],

];
