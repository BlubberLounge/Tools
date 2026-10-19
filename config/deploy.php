<?php

return [
    'webhook_secret' => env('DEPLOY_WEBHOOK_SECRET'),

    // CLI binary for app:deploy. Empty: the webhook picks it at runtime (see
    // DeployWebhookController::phpBinary); not resolved here because config:cache
    // runs over SSH, where other PHP builds exist than in the web process.
    'php_binary' => env('DEPLOY_PHP_BINARY'),
];
