<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 */
#[Fillable(['title', 'slug', 'content', 'content_json', 'excerpt', 'status', 'meta_title', 'meta_description', 'meta_keywords', 'user_id', 'published_at', 'featured_image', 'category_id'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {                                    
        return $this->belongsTo(Category::class);
    }  

    public function tags(): BelongsToMany
    {                                                                                                                                                                                                                                                                        
        return $this->belongsToMany(Tag::class);
    }
    
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published);
    }
}
