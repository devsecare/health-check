<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    protected $fillable = [
        'name',
        'url',
    ];

    /**
     * Get the PageSpeed insights for the website
     */
    public function pageSpeedInsights(): HasMany
    {
        return $this->hasMany(PageSpeedInsight::class);
    }

    /**
     * Get the latest PageSpeed insight
     */
    public function latestPageSpeedInsight($strategy = 'mobile')
    {
        return $this->pageSpeedInsights()
            ->where('strategy', $strategy)
            ->latest()
            ->first();
    }

    /**
     * Get SEO audits for the website
     */
    public function seoAudits(): HasMany
    {
        return $this->hasMany(SeoAudit::class);
    }

    /**
     * Get the latest SEO audit
     */
    public function latestSeoAudit()
    {
        return $this->seoAudits()->latest()->first();
    }

    /**
     * Get broken links checks for the website
     */
    public function brokenLinksChecks(): HasMany
    {
        return $this->hasMany(BrokenLink::class);
    }

    /**
     * Get the latest broken links check
     */
    public function latestBrokenLinksCheck()
    {
        return $this->brokenLinksChecks()->latest()->first();
    }
}
