<?php

// ...existing code...

Route::middleware(['custom.cors', 'throttle:60,1'])->group(function () {
    // ...existing routes...
});

// ...existing code...

Route::get('/test', function () {
    return response('OK', 200);
});
