<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_can_edit_returns_true_for_super_admin(): void
    {
        $this->assertTrue(UserRole::SuperAdmin->canEdit());
    }

    public function test_can_edit_returns_true_for_editor(): void
    {
        $this->assertTrue(UserRole::Editor->canEdit());
    }

    public function test_can_edit_returns_false_for_viewer(): void
    {
        $this->assertFalse(UserRole::Viewer->canEdit());
    }

    public function test_can_manage_users_returns_true_for_super_admin(): void
    {
        $this->assertTrue(UserRole::SuperAdmin->canManageUsers());
    }

    public function test_can_manage_users_returns_false_for_editor(): void
    {
        $this->assertFalse(UserRole::Editor->canManageUsers());
    }

    public function test_can_manage_users_returns_false_for_viewer(): void
    {
        $this->assertFalse(UserRole::Viewer->canManageUsers());
    }

    public function test_can_manage_settings_returns_true_for_super_admin(): void
    {
        $this->assertTrue(UserRole::SuperAdmin->canManageSettings());
    }

    public function test_can_manage_settings_returns_false_for_editor(): void
    {
        $this->assertFalse(UserRole::Editor->canManageSettings());
    }

    public function test_can_manage_settings_returns_false_for_viewer(): void
    {
        $this->assertFalse(UserRole::Viewer->canManageSettings());
    }
}
