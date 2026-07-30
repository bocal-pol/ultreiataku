<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\GuideCategory;
use Database\Factories\GuideSectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class GuideSection extends Model
{
    /** @use HasFactory<GuideSectionFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUuids;

    /** @var list<string> */
    public array $translatable = ['title', 'content'];

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'category',
        'title',
        'icon',
        'content',
        'sort_order',
        'is_published',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'category' => GuideCategory::class,
        'sort_order' => 'integer',
        'is_published' => 'boolean',
    ];

    protected static function newFactory(): GuideSectionFactory
    {
        return GuideSectionFactory::new();
    }

    /**
     * Scope : sections publiées uniquement.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
