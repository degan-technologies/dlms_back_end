<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Announcement;

class AnnouncementPolicy
{
    /**
     * Superadmin can do anything
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }
    }

    /**
     * Anyone authenticated can view the announcement list
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * A user can view:
     * - Their own announcements
     * - Published announcements
     * - Admin and librarian can view all
     */
    public function view(User $user, Announcement $announcement): bool
    {
        return
            $announcement->is_published ||
            $user->id === $announcement->user_id ||
            $user->hasAnyRole(['admin', 'librarian']);
    }

    /**
     * Only superadmin and admin can create announcements
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Only owner can update (admin/librarian can only update their own)
     */
    public function update(User $user, Announcement $announcement): bool
    {
        return $user->id === $announcement->user_id;
    }

    /**
     * Only owner can delete (admin/librarian can only delete their own)
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->id === $announcement->user_id;
    }

    /**
     * Optional: Only owner can restore
     */
    public function restore(User $user, Announcement $announcement): bool
    {
        return $user->id === $announcement->user_id;
    }

    /**
     * Optional: Only owner can force delete
     */
    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return $user->id === $announcement->user_id;
    }
}
