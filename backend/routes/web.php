<?php

use App\Modules\OAuth\Services\CentralAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route nommée `login` — EXIGÉE par Laravel : le middleware `auth` y redirige
// tout invité sur une requête non-JSON. Ultreiataku n'a pas de login local
// (SSO central) : on redirige vers l'UI de connexion de l'Auth central.
// Les requêtes API (/api/*) reçoivent un 401 JSON via le handler de bootstrap/app.php
// et n'atteignent jamais cette route.
Route::get('/login', function (Request $request) {
    /** @var CentralAuthService $centralAuth */
    $centralAuth = app(CentralAuthService::class);

    $state = bin2hex(random_bytes(32));
    $request->session()->put('oauth_state', $state);

    return redirect(
        $centralAuth->loginUrl()
        . '?app=' . urlencode($centralAuth->appSlug())
        . '&return=' . urlencode($centralAuth->callbackUrl())
        . '&state=' . urlencode($state)
    );
})->name('login');
