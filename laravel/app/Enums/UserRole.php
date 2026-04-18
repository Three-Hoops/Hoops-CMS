<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function canEdit(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Editor]);
    }

    public function canManageUsers(): bool
    {
        return $this === self::SuperAdmin;
    }
}

?>