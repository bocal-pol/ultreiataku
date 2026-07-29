<?php

use App\Modules\Pilgrimage\Providers\PilgrimageServiceProvider;
use App\Modules\Vault\Providers\VaultServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    // RÈGLE ABSOLUE : VaultServiceProvider TOUJOURS PREMIER
    // Override la config AVANT que les connexions DB/Redis/MinIO soient ouvertes.
    VaultServiceProvider::class,

    AppServiceProvider::class,
    AdminPanelProvider::class,
    PilgrimageServiceProvider::class,
];
