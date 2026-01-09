<?php

namespace App\Traits;

use App\Models\Website;
use Illuminate\Support\Facades\Auth;

trait HasWebsiteAccess
{
    /**
     * Get the current authenticated user
     */
    protected function getCurrentUser()
    {
        return Auth::user();
    }

    /**
     * Get accessible websites for current user
     */
    protected function getAccessibleWebsites()
    {
        $user = $this->getCurrentUser();

        if ($user->isSuperAdmin()) {
            return Website::all();
        }

        return $user->websites;
    }

    /**
     * Get accessible website IDs for current user
     */
    protected function getAccessibleWebsiteIds(): array
    {
        $user = $this->getCurrentUser();
        return $user->getAccessibleWebsiteIds();
    }

    /**
     * Check if current user can access a website
     */
    protected function canAccessWebsite(int $websiteId): bool
    {
        $user = $this->getCurrentUser();
        return $user->canAccessWebsite($websiteId);
    }

    /**
     * Ensure user has access to website, abort if not
     */
    protected function ensureWebsiteAccess(int $websiteId): void
    {
        if (!$this->canAccessWebsite($websiteId)) {
            abort(403, 'You do not have access to this website.');
        }
    }
}
