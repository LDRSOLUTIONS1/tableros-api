<?php

return [
    'secret_rh' => env('JWT_SECRET'),
    'cookie_name' => env('JWT_COOKIE_NAME', 'token'),
    'require_intranet_cookie' => filter_var(
        env('AUTH_REQUIRE_INTRANET_COOKIE', false),
        FILTER_VALIDATE_BOOLEAN
    ),
];
