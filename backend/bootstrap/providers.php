<?php

return [
    // RÈGLE ABSOLUE : VaultServiceProvider TOUJOURS PREMIER
    // Override la config AVANT que les connexions DB/Redis/MinIO soient ouvertes.
    App\Modules\Vault\Providers\VaultServiceProvider::class,

    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Modules\Pilgrimage\Providers\PilgrimageServiceProvider::class,
];
