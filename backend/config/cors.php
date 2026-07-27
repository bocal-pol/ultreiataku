<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS — Cross-Origin Resource Sharing
    |--------------------------------------------------------------------------
    |
    | P0-01 (SEC-ULTREIA-AUTH) — CORS aligné sur le pattern monorepo SiteV26.
    | Source de référence : Oikotaku/backend/config/cors.php.
    |
    | allowed_origins : NE PAS utiliser ['*'] en production.
    | Renseigner les domaines réels via .env :
    |   FRONTEND_URL=https://ultreiataku.policeliege.be
    |   AUTH_ADMIN_URL=https://auth.policeliege.be
    |
    | En local (dev) les valeurs viennent du .env local.
    | En prod, seules les origines listées ci-dessous sont autorisées.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // SEC-ULTREIA-CORS-01 — CWE-346
    // Aucun défaut localhost : les origines viennent exclusivement de l'env.
    // En prod, si FRONTEND_URL / AUTH_ADMIN_URL sont absents, la liste est vide
    // (allowlist stricte). Les dev renseignent leurs URLs dans .env local.
    'allowed_origins' => array_filter([
        env('FRONTEND_URL'),
        env('AUTH_ADMIN_URL'),
    ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Accept',
        'Accept-Language',
        'Cache-Control',
    ],

    'exposed_headers' => [],

    'max_age' => 7200,

    // Obligatoire pour que le navigateur envoie le cookie de session avec
    // les requêtes cross-origin (frontend SPA → backend API).
    // Couplé à credentials: 'include' côté fetch / axios.
    'supports_credentials' => true,

];
