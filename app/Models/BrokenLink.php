<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokenLink extends Model
{
    protected $table = 'broken_links_checks';
    
    protected $fillable = [
        'website_id',
        'url',
        'progress',
        'status',
        'job_id',
        'summary',
        'broken_links_data',
        'total_checked',
        'total_broken',
        'raw_data',
    ];

    protected $casts = [
        'summary' => 'array',
        'broken_links_data' => 'array',
        'total_checked' => 'integer',
        'total_broken' => 'integer',
    ];

    /**
     * Get the website that owns the broken links check
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get broken links grouped by type
     */
    public function getBrokenLinksByTypeAttribute()
    {
        if (!$this->broken_links_data) {
            return [];
        }

        $grouped = [];
        foreach ($this->broken_links_data as $link) {
            $type = $link['type'] ?? 'link';
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $link;
        }

        return $grouped;
    }

    /**
     * Get broken links grouped by status code
     */
    public function getBrokenLinksByStatusCodeAttribute()
    {
        if (!$this->broken_links_data) {
            return [];
        }

        $grouped = [];
        foreach ($this->broken_links_data as $link) {
            $code = $link['status_code'] ?? 0;
            if (!isset($grouped[$code])) {
                $grouped[$code] = [];
            }
            $grouped[$code][] = $link;
        }

        return $grouped;
    }
}
