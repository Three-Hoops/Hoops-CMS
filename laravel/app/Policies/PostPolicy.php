<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return $user->role->canEdit();
    }

    public function create(User $user): bool
    {
        return $user->role->canEdit();
    }

    public function update(User $user, Post $post): bool
    {
        return $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::Editor && $post->user_id === $user->id);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::Editor && $post->user_id === $user->id);
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::Editor && $post->user_id === $user->id);
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }
}
