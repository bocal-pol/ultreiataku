<?php

use App\Modules\OAuth\Http\Controllers\SsoCallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ULTREIA-03 — Route SSO callback Filament admin (ADR-U06)
|--------------------------------------------------------------------------
|
| Cette route DOIT être déclarée AVANT le panel Filament dans le routing
| pour intercepter le retour Auth AVANT que Filament n'applique son auth.
|
| Flux : Auth SSO → GET /admin/sso/callback?code=...&state=...
|         → SsoCallbackController → session Filament → /admin
|
*/

Route::get('/admin/sso/callback', SsoCallbackController::class)
    ->middleware(['web'])
    ->name('admin.sso.callback');
