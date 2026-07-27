<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Rendre toutes les erreurs en JSON pour les routes /api/*
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // P0-01 (SEC-ULTREIA-AUTH) — AuthenticationException → JSON 401 sur /api/*
        // Sans ce handler, Laravel redirigerait vers route('login') inexistante.
        // Le SPA frontend intercepte le 401 et redirige vers le SSO central.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'unauthenticated',
                    'message' => 'Authentification requise.',
                    'status' => 401,
                ], 401);
            }

            return null;
        });
    })->create();
