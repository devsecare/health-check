<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Website;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Create a notification for broken links check completion
     */
    public static function notifyBrokenLinksCheckCompleted(Website $website, int $totalBroken, int $totalChecked, ?int $userId = null): void
    {
        // If no user ID provided, try to get from auth, otherwise create global notification
        $userId = $userId ?? Auth::id();

        if ($totalBroken > 0) {
            Notification::create([
                'user_id' => $userId,
                'type' => 'broken_links',
                'title' => 'Broken Links Found',
                'message' => "Found {$totalBroken} broken link(s) on {$website->name} (checked {$totalChecked} pages)",
                'url' => route('admin.websites.broken-links', $website),
                'data' => [
                    'website_id' => $website->id,
                    'total_broken' => $totalBroken,
                    'total_checked' => $totalChecked,
                ],
            ]);
        } else {
            Notification::create([
                'user_id' => $userId,
                'type' => 'broken_links',
                'title' => 'Broken Links Check Completed',
                'message' => "No broken links found on {$website->name} (checked {$totalChecked} pages)",
                'url' => route('admin.websites.broken-links', $website),
                'data' => [
                    'website_id' => $website->id,
                    'total_broken' => 0,
                    'total_checked' => $totalChecked,
                ],
            ]);
        }
    }

    /**
     * Create a notification for SEO audit completion
     */
    public static function notifySeoAuditCompleted(Website $website, int $score, ?int $userId = null): void
    {
        // If no user ID provided, try to get from auth, otherwise create global notification
        $userId = $userId ?? Auth::id();

        Notification::create([
            'user_id' => $userId,
            'type' => 'seo_audit',
            'title' => 'SEO Audit Completed',
            'message' => "SEO audit completed for {$website->name} with score: {$score}/100",
            'url' => route('admin.websites.seo-audit', $website),
            'data' => [
                'website_id' => $website->id,
                'score' => $score,
            ],
        ]);
    }

    /**
     * Create a notification for PageSpeed test completion
     */
    public static function notifyPageSpeedTestCompleted(Website $website, int $performanceScore, string $strategy = 'mobile', ?int $userId = null): void
    {
        // If no user ID provided, try to get from auth, otherwise create global notification
        $userId = $userId ?? Auth::id();

        Notification::create([
            'user_id' => $userId,
            'type' => 'pagespeed',
            'title' => 'PageSpeed Test Completed',
            'message' => "PageSpeed test completed for {$website->name} ({$strategy}): {$performanceScore}/100",
            'url' => route('admin.websites.pagespeed', $website),
            'data' => [
                'website_id' => $website->id,
                'performance_score' => $performanceScore,
                'strategy' => $strategy,
            ],
        ]);
    }

    /**
     * Create a notification for Domain Authority check completion
     */
    public static function notifyDomainAuthorityCheckCompleted(Website $website, int $domainAuthority, ?int $userId = null): void
    {
        // If no user ID provided, try to get from auth, otherwise create global notification
        $userId = $userId ?? Auth::id();

        Notification::create([
            'user_id' => $userId,
            'type' => 'domain_authority',
            'title' => 'Domain Authority Check Completed',
            'message' => "Domain Authority check completed for {$website->name}: DA {$domainAuthority}/100",
            'url' => route('admin.websites.domain-authority', $website),
            'data' => [
                'website_id' => $website->id,
                'domain_authority' => $domainAuthority,
            ],
        ]);
    }

    /**
     * Create a general notification
     */
    public static function notify(?int $userId, string $type, string $title, string $message, ?string $url = null, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'data' => $data,
        ]);
    }
}

