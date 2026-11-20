<?php

return [
    // queue: lo procesa un worker (php artisan queue:work).
    // after_response: se ejecuta tras responder, sin worker.
    'mode' => env('EXCEL_IMPORT_MODE', 'after_response'),
];
