<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\ContentStatus;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => fake()->title(),
            'slug' => Str::slug($title),
            'content' => fake()->sentence(),
            'content_json' => [],
            'excerpt' => fake()->sentence(),
            'status'=> fake()->randomElement(ContentStatus::cases()),
            'user_id'=> User::factory(),
            'published_at' => null,
        ];
    }
}
