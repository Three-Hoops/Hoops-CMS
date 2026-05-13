<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Enums\ContentStatus;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->sentence(),
            'content_json' => [],
            'excerpt' => fake()->sentence(),
            'status' => fake()->randomElement(ContentStatus::cases()),
            'user_id' => User::factory(),
            'published_at' => null,
        ];
    }
}
