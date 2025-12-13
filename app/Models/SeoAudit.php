<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoAudit extends Model
{
    protected $fillable = [
        'website_id',
        'url',
        'meta_tags',
        'headings',
        'images',
        'url_structure',
        'internal_links',
        'schema_markup',
        'open_graph',
        'robots_txt',
        'sitemap',
        'overall_score',
        'raw_data',
    ];

    protected $casts = [
        'meta_tags' => 'array',
        'headings' => 'array',
        'images' => 'array',
        'url_structure' => 'array',
        'internal_links' => 'array',
        'schema_markup' => 'array',
        'open_graph' => 'array',
        'robots_txt' => 'array',
        'sitemap' => 'array',
        'overall_score' => 'integer',
    ];

    /**
     * Get the website that owns the SEO audit
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Calculate overall SEO score
     */
    public function calculateOverallScore(): int
    {
        $score = 0;
        $maxScore = 100;
        $pointsPerCheck = $maxScore / 9; // 9 main checks

        // Meta tags (title + description)
        if (($this->meta_tags['title']['exists'] ?? false) && ($this->meta_tags['title']['status'] ?? 'error') === 'good') {
            $score += $pointsPerCheck * 0.5;
        }
        if (($this->meta_tags['description']['exists'] ?? false) && ($this->meta_tags['description']['status'] ?? 'error') === 'good') {
            $score += $pointsPerCheck * 0.5;
        }

        // Headings (H1 status)
        if (($this->headings['h1_status']['status'] ?? 'error') === 'good') {
            $score += $pointsPerCheck;
        }

        // Images (alt attributes)
        if (($this->images['status'] ?? 'error') === 'good') {
            $score += $pointsPerCheck;
        }

        // URL structure
        if (($this->url_structure['status'] ?? 'error') === 'good') {
            $score += $pointsPerCheck;
        }

        // Schema markup
        if (($this->schema_markup['has_schema'] ?? false)) {
            $score += $pointsPerCheck;
        }

        // Open Graph
        $ogStatus = $this->open_graph['status'] ?? 'error';
        if ($ogStatus === 'good') {
            $score += $pointsPerCheck;
        } elseif ($ogStatus === 'warning') {
            $score += $pointsPerCheck * 0.5;
        }

        // Robots.txt
        if (($this->robots_txt['exists'] ?? false)) {
            $score += $pointsPerCheck;
        }

        // Sitemap
        if (($this->sitemap['exists'] ?? false)) {
            $score += $pointsPerCheck;
        }

        return (int) round(min($score, $maxScore));
    }
}
