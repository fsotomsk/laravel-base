<?php

return [
    'domains' => preg_split('#([^0-9a-z-_.]+)#i', env('SSL_DOMAINS') ?: env('APP_DOMAINS')),
    'email'   => env('SSL_EMAIL') ?: env('APP_ADMIN_EMAIL'),
];
