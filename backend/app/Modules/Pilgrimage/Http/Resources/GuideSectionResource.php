<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\GuideSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var GuideSection $this */
        $locale = $request->header('Accept-Language', 'fr');
        $locale = in_array($locale, ['fr', 'nl', 'de']) ? $locale : 'fr';

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'category' => $this->category?->value,
            'category_label' => $this->category?->label(),
            'title' => $this->getTranslation('title', $locale, false)
                ?? $this->getTranslation('title', 'fr', false),
            'icon' => $this->icon,
            'content' => $this->getTranslation('content', $locale, false)
                ?? $this->getTranslation('content', 'fr', false),
            'sort_order' => $this->sort_order,
        ];
    }
}
