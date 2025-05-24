<?php

return [

    'paths' => ['api/*', 'login', 'logout', 'register', 'oauth/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:5173'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization', 'Content-Type'],

    'max_age' => 0,

    'supports_credentials' => true,

];
