<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Este arquivo armazena as credenciais de serviços de terceiros como
    | Mailgun, Postmark, AWS, etc. Ele atua como um local padrão para todas
    | essas credenciais, permitindo centralizar e organizar o acesso.
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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuração do Frontend
    |--------------------------------------------------------------------------
    |
    | Aqui definimos a URL do seu aplicativo frontend, usada principalmente
    | para o link de redefinição de senha nos e-mails. Essa configuração
    | ajuda a manter a separação entre backend (Laravel) e frontend (SPA).
    |
    | Exemplo: http://localhost:3000 ou https://app.seusite.com
    |
    */

    'frontend' => [
        'url' => env('FRONTEND_URL', 'http://localhost:3000'),
    ],

];