<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\GuideCategory;
use App\Modules\Pilgrimage\Models\GuideSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GuideSection>
 */
class GuideSectionFactory extends Factory
{
    protected $model = GuideSection::class;

    public function definition(): array
    {
        $title = $this->faker->words(3, true);

        return [
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'category' => $this->faker->randomElement(GuideCategory::cases())->value,
            'title' => [
                'fr' => ucfirst($title),
                'nl' => ucfirst($title),
                'de' => ucfirst($title),
            ],
            'icon' => 'heroicon-o-book-open',
            'content' => [
                'fr' => $this->faker->paragraphs(3, true),
                'nl' => $this->faker->paragraphs(3, true),
                'de' => $this->faker->paragraphs(3, true),
            ],
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    public function withCategory(GuideCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category->value,
        ]);
    }
}
