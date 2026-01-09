<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'website_limit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get notifications for the user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count
     */
    public function unreadNotificationsCount(): int
    {
        return Notification::where(function($q) {
                $q->where('user_id', $this->id)
                  ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->count();
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Get websites assigned to this user
     */
    public function websites()
    {
        return $this->belongsToMany(Website::class, 'user_website')->withTimestamps();
    }

    /**
     * Get accessible website IDs for this user
     * Super admin gets all, regular users get only assigned
     */
    public function getAccessibleWebsiteIds(): array
    {
        if ($this->isSuperAdmin()) {
            return Website::pluck('id')->toArray();
        }

        return $this->websites()->pluck('websites.id')->toArray();
    }

    /**
     * Check if user can access a specific website
     */
    public function canAccessWebsite(int $websiteId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->websites()->where('websites.id', $websiteId)->exists();
    }

    /**
     * Check if user can create more websites
     */
    public function canCreateWebsite(): bool
    {
        if ($this->isSuperAdmin()) {
            return true; // Super admin has unlimited websites
        }

        // If limit is null, user has unlimited websites
        if ($this->website_limit === null) {
            return true;
        }

        // Check if user has reached their limit
        $currentWebsiteCount = $this->websites()->count();
        return $currentWebsiteCount < $this->website_limit;
    }

    /**
     * Get remaining website slots for user
     */
    public function getRemainingWebsiteSlots(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null; // Unlimited
        }

        if ($this->website_limit === null) {
            return null; // Unlimited
        }

        $currentWebsiteCount = $this->websites()->count();
        return max(0, $this->website_limit - $currentWebsiteCount);
    }
}
