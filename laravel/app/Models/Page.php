<?php

namespace App\Models;

use App\Models\User;
use App\Traits\SanitisesContent;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 */
#[Fillable(['title', 'slug', 'content', 'content_json', 'excerpt', 'status', 'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'user_id', 'published_at', 'parent_id'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, SoftDeletes, SanitisesContent;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $hidden = ['autosave_json'];

    protected function casts(): array
    {
        return [
            'published_at'  => 'datetime',
            'status'        => ContentStatus::class,
            'content_json'  => 'array',
            'autosave_json' => 'array',
        ];
    }

    public function setContentAttribute(?string $value): void
    {
        $this->attributes['content'] = $this->sanitiseHtml($value);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    //prevent circular references
    public function isDescendantOf(Page $page): bool {
        $current = $this->parent;
        while($current !== null) {
            if($current->id === $page->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }
}
