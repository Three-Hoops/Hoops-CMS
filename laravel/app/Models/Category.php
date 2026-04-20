<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/**
 * @property UserRole $role
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property bool $is_active
 * @property string $locale
 */
#[Fillable(['name', 'slug', 'description', 'parent_id'])]
class Category
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
            'is_active'         => 'boolean',
            'theme_mode'        => ThemeMode::class,
        ];
    }
}
