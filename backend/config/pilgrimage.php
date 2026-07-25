<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Simplification GPX — Douglas-Peucker (ADR-U05)
    |--------------------------------------------------------------------------
    */
    'gpx' => [
        'simplification_tolerance' => env('PILGRIMAGE_GPX_TOLERANCE', 0.0001),
        'cache_ttl_seconds' => env('PILGRIMAGE_GPX_CACHE_TTL', 86400), // 24h
    ],

    /*
    |--------------------------------------------------------------------------
    | Disks MinIO (ADR-U02)
    |--------------------------------------------------------------------------
    */
    'disks' => [
        'gpx' => env('PILGRIMAGE_DISK_GPX', 'minio_gpx'),
        'journal' => env('PILGRIMAGE_DISK_JOURNAL', 'minio_journal'),
        'images' => env('PILGRIMAGE_DISK_IMAGES', 'minio_images'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hébergements — Alerte vérification RG-08
    |--------------------------------------------------------------------------
    */
    'accommodation' => [
        'verification_months' => 6,
    ],
];
