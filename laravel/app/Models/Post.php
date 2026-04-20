<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Enums\ContentStatus;

/**
 */
#[Fillable(['title', 'slug', 'content', 'content_json', 'excerpt', 'status', 'meta_title', 'meta_description', 'meta_keywords', 'user_id', 'published_at'])]
class Post
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at'  => 'datetime',
            'status'        => ContentStatus::class,
            'content_json'  => 'array',
        ];
    }
}
