<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->role->canEdit();
    }

    public function create(User $user): bool
    {
        return $user->role->canEdit();
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->role->canEdit();
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->role->canEdit();
    }
}
