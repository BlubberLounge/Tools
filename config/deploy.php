<?php

return [
    'webhook_secret' => env('DEPLOY_WEBHOOK_SECRET'),

    // CLI binary for app:deploy. The web PHP_BINARY can be a CGI build; IONOS
    // needs the versioned CLI, e.g. php8.4-cli.
    'php_binary' => env('DEPLOY_PHP_BINARY') ?: PHP_BINARY,
];
