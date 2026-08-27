<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| PropSpace is an API-only backend. The user interface is the Vue SPA in
| /frontend, which authenticates against routes/api.php using Sanctum tokens.
| The previous Blade/session login screens were removed in favour of that.
|
*/

Route::get('/', function () {
    return response()->json([
        'name'    => config('app.name'),
        'api'     => url('/api'),
        'status'  => 'ok',
    ]);
});
