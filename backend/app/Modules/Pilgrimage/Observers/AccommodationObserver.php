<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Observers;

use App\Modules\Pilgrimage\Models\Accommodation;
use Illuminate\Support\Facades\Log;

/**
 * Observer Accommodation — log RG-08 (verified_at badge) à chaque save.
 */
final class AccommodationObserver
{
    public function saved(Accommodation $accommodation): void
    {
        if ($accommodation->isObsolete()) {
            Log::warning('Accommodation obsolète (RG-08)', [
                'accommodation_id' => $accommodation->id,
                'name_fr' => $accommodation->getTranslation('name', 'fr'),
                'verified_at' => $accommodation->verified_at?->toIso8601String(),
            ]);
        }
    }
}
