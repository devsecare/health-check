<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSpeedInsight extends Model
{
    protected $table = 'pagespeed_insights';
    
    protected $fillable = [
        'website_id',
        'strategy',
        'performance_score',
        'accessibility_score',
        'seo_score',
        'best_practices_score',
        'lcp',
        'fcp',
        'cls',
        'tbt',
        'si',
        'ttfb',
        'interactive',
        'raw_data',
    ];

    protected $casts = [
        'performance_score' => 'integer',
        'accessibility_score' => 'integer',
        'seo_score' => 'integer',
        'best_practices_score' => 'integer',
        'lcp' => 'decimal:2',
        'fcp' => 'decimal:2',
        'cls' => 'decimal:2',
        'tbt' => 'decimal:2',
        'si' => 'decimal:2',
        'ttfb' => 'decimal:2',
        'interactive' => 'decimal:2',
    ];

    /**
     * Get the website that owns the PageSpeed insight
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get parsed opportunities from raw_data
     */
    public function getOpportunitiesAttribute()
    {
        $rawData = json_decode($this->raw_data, true);
        if (!$rawData) {
            return [];
        }

        $audits = $rawData['lighthouseResult']['audits'] ?? [];
        return $this->extractOpportunities($audits);
    }

    /**
     * Get parsed diagnostics from raw_data
     */
    public function getDiagnosticsAttribute()
    {
        $rawData = json_decode($this->raw_data, true);
        if (!$rawData) {
            return [];
        }

        $audits = $rawData['lighthouseResult']['audits'] ?? [];
        return $this->extractDiagnostics($audits);
    }

    /**
     * Get screenshot data from raw_data
     */
    public function getScreenshotsAttribute()
    {
        $rawData = json_decode($this->raw_data, true);
        if (!$rawData) {
            return null;
        }

        $lighthouseResult = $rawData['lighthouseResult'] ?? [];
        $screenshots = [];
        
        // Helper function to normalize screenshot data
        $normalizeScreenshot = function($data) {
            if (empty($data)) {
                return null;
            }
            
            // If it's already a data URI, return as is
            if (is_string($data) && str_starts_with($data, 'data:')) {
                return $data;
            }
            
            // If it's raw base64, detect format and create data URI
            // Try to detect format from base64 header
            $decoded = base64_decode(substr($data, 0, 100), true);
            if ($decoded) {
                // Check for WebP (RIFF header)
                if (str_starts_with($decoded, 'RIFF') && strpos($decoded, 'WEBP') !== false) {
                    return 'data:image/webp;base64,' . $data;
                }
                // Check for PNG
                if (str_starts_with($decoded, "\x89PNG")) {
                    return 'data:image/png;base64,' . $data;
                }
                // Check for JPEG
                if (str_starts_with($decoded, "\xFF\xD8\xFF")) {
                    return 'data:image/jpeg;base64,' . $data;
                }
            }
            
            // Default to JPEG if we can't detect
            return 'data:image/jpeg;base64,' . $data;
        };
        
        // Check for final screenshot
        if (isset($lighthouseResult['audits']['final-screenshot']['details']['data'])) {
            $finalData = $lighthouseResult['audits']['final-screenshot']['details']['data'];
            $normalized = $normalizeScreenshot($finalData);
            if ($normalized) {
                $screenshots['final'] = $normalized;
            }
        }
        
        // Check for screenshot thumbnails (timeline)
        if (isset($lighthouseResult['audits']['screenshot-thumbnails']['details']['items'])) {
            $thumbnails = $lighthouseResult['audits']['screenshot-thumbnails']['details']['items'];
            $normalizedThumbnails = [];
            foreach ($thumbnails as $thumbnail) {
                if (isset($thumbnail['data'])) {
                    $normalized = $normalizeScreenshot($thumbnail['data']);
                    if ($normalized) {
                        $normalizedThumbnails[] = [
                            'data' => $normalized,
                            'timing' => $thumbnail['timing'] ?? null,
                        ];
                    }
                }
            }
            if (!empty($normalizedThumbnails)) {
                $screenshots['thumbnails'] = $normalizedThumbnails;
            }
        }
        
        // Check for full-page screenshot
        if (isset($lighthouseResult['fullPageScreenshot']['screenshot']['data'])) {
            $fullPageData = $lighthouseResult['fullPageScreenshot']['screenshot']['data'];
            $normalized = $normalizeScreenshot($fullPageData);
            if ($normalized) {
                $screenshots['fullPage'] = $normalized;
            }
        }
        
        return !empty($screenshots) ? $screenshots : null;
    }

    /**
     * Get detailed render-blocking resources
     */
    public function getRenderBlockingResourcesAttribute()
    {
        $rawData = json_decode($this->raw_data, true);
        if (!$rawData) {
            return null;
        }

        $audits = $rawData['lighthouseResult']['audits'] ?? [];
        $renderBlocking = $audits['render-blocking-resources'] ?? null;
        
        if (!$renderBlocking || !isset($renderBlocking['details']['items'])) {
            return null;
        }

        return [
            'title' => $renderBlocking['title'] ?? 'Render blocking requests',
            'description' => $renderBlocking['description'] ?? '',
            'displayValue' => $renderBlocking['displayValue'] ?? '',
            'numericValue' => $renderBlocking['numericValue'] ?? 0,
            'wastedMs' => $renderBlocking['numericValue'] ?? 0,
            'items' => $renderBlocking['details']['items'] ?? [],
        ];
    }

    /**
     * Get all insights with resource details (like Google PageSpeed Insights)
     */
    public function getAllInsightsWithResources()
    {
        $rawData = json_decode($this->raw_data, true);
        if (!$rawData) {
            return [];
        }

        $audits = $rawData['lighthouseResult']['audits'] ?? [];
        $insights = [];

        // List of audits that typically have resource details
        $insightAudits = [
            'render-blocking-resources' => [
                'type' => 'opportunity',
                'hasResources' => true,
                'resourceFields' => ['url', 'totalBytes', 'wastedMs'],
            ],
            'unused-css-rules' => [
                'type' => 'opportunity',
                'hasResources' => true,
                'resourceFields' => ['url', 'totalBytes', 'wastedBytes'],
            ],
            'unused-javascript' => [
                'type' => 'opportunity',
                'hasResources' => true,
                'resourceFields' => ['url', 'totalBytes', 'wastedBytes'],
            ],
            'modern-image-formats' => [
                'type' => 'opportunity',
                'hasResources' => true,
                'resourceFields' => ['url', 'totalBytes', 'wastedBytes'],
            ],
            'uses-optimized-images' => [
                'type' => 'opportunity',
                'hasResources' => true,
                'resourceFields' => ['url', 'totalBytes', 'wastedBytes'],
            ],
            'offscreen-images' => [
                'type' => 'opportunity',
                'hasResources' => true,
                'resourceFields' => ['url', 'totalBytes', 'wastedBytes'],
            ],
            'unminified-css' => [
                'type' => 'opportunity',
                'hasResources' => true,
                'resourceFields' => ['url', 'totalBytes', 'wastedBytes'],
            ],
            'unminified-javascript' => [
                'type' => 'opportunity',
                'hasResources' => true,
                'resourceFields' => ['url', 'totalBytes', 'wastedBytes'],
            ],
        ];

        foreach ($insightAudits as $auditKey => $config) {
            $audit = $audits[$auditKey] ?? null;
            
            if (!$audit) {
                continue;
            }

            // Only include if score exists and is less than 1 (has issues)
            if (isset($audit['score']) && $audit['score'] !== null && $audit['score'] < 1) {
                $insight = [
                    'key' => $auditKey,
                    'title' => $audit['title'] ?? '',
                    'description' => $audit['description'] ?? '',
                    'displayValue' => $audit['displayValue'] ?? '',
                    'numericValue' => $audit['numericValue'] ?? 0,
                    'score' => $audit['score'],
                    'type' => $config['type'],
                    'hasResources' => false,
                    'items' => [],
                ];

                // Check if it has resource details
                if ($config['hasResources'] && isset($audit['details']['items']) && !empty($audit['details']['items'])) {
                    $insight['hasResources'] = true;
                    $insight['items'] = $audit['details']['items'];
                    
                    // Calculate wasted time/bytes from items (more accurate than numericValue)
                    if ($auditKey === 'render-blocking-resources') {
                        // For render-blocking, wastedMs is in numericValue (total)
                        $insight['wastedMs'] = $audit['numericValue'] ?? 0;
                        $insight['wastedBytes'] = 0;
                    } else {
                        // For others, sum up wastedBytes from each item
                        $insight['wastedMs'] = 0;
                        $totalWastedBytes = 0;
                        foreach ($audit['details']['items'] as $item) {
                            $totalWastedBytes += ($item['wastedBytes'] ?? 0);
                        }
                        // Use the calculated sum, or fallback to numericValue if sum is 0
                        $insight['wastedBytes'] = $totalWastedBytes > 0 ? $totalWastedBytes : ($audit['numericValue'] ?? 0);
                    }
                } else {
                    // Even without resource details, we might want to show the insight
                    // For now, only include insights with resources in the Insights section
                    // But calculate wasted bytes from numericValue if available
                    if ($auditKey === 'render-blocking-resources') {
                        $insight['wastedMs'] = $audit['numericValue'] ?? 0;
                        $insight['wastedBytes'] = 0;
                    } else {
                        $insight['wastedMs'] = 0;
                        $insight['wastedBytes'] = $audit['numericValue'] ?? 0;
                    }
                }

                $insights[$auditKey] = $insight;
            }
        }

        // Sort by wastedMs or wastedBytes (descending)
        usort($insights, function($a, $b) {
            $aValue = $a['wastedMs'] > 0 ? $a['wastedMs'] : $a['wastedBytes'];
            $bValue = $b['wastedMs'] > 0 ? $b['wastedMs'] : $b['wastedBytes'];
            return $bValue <=> $aValue;
        });

        return $insights;
    }

    /**
     * Group resources by domain/CDN
     */
    public function groupResourcesByDomain($resources, $websiteUrl = null, $resourceType = 'render-blocking')
    {
        $grouped = [];
        
        // Get website URL from relationship or parameter
        $websiteUrl = $websiteUrl ?? ($this->website->url ?? '');
        
        foreach ($resources as $resource) {
            $url = $resource['url'] ?? '';
            $domain = parse_url($url, PHP_URL_HOST) ?? 'unknown';
            
            // Determine if it's 1st party or 3rd party
            $isFirstParty = $this->isFirstParty($domain, $websiteUrl);
            $groupKey = $isFirstParty ? $domain : $this->getCdnName($domain);
            
            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'domain' => $groupKey,
                    'isFirstParty' => $isFirstParty,
                    'isCdn' => !$isFirstParty,
                    'resources' => [],
                    'totalSize' => 0,
                    'totalDuration' => 0,
                    'totalWastedBytes' => 0,
                ];
            }
            
            $size = $resource['totalBytes'] ?? 0;
            $duration = $resource['wastedMs'] ?? 0;
            $wastedBytes = $resource['wastedBytes'] ?? 0;
            
            $grouped[$groupKey]['resources'][] = $resource;
            $grouped[$groupKey]['totalSize'] += $size;
            $grouped[$groupKey]['totalDuration'] += $duration;
            $grouped[$groupKey]['totalWastedBytes'] += $wastedBytes;
        }
        
        return $grouped;
    }

    /**
     * Check if domain is first party
     */
    private function isFirstParty($domain, $websiteUrl = null)
    {
        $websiteUrl = $websiteUrl ?? ($this->website->url ?? '');
        $websiteDomain = parse_url($websiteUrl, PHP_URL_HOST) ?? '';
        
        if (empty($websiteDomain)) {
            return false;
        }
        
        return $domain === $websiteDomain || 
               str_ends_with($domain, '.' . $websiteDomain) ||
               str_ends_with($websiteDomain, '.' . $domain);
    }

    /**
     * Get CDN name from domain
     */
    private function getCdnName($domain)
    {
        $cdnMap = [
            'cdnjs.cloudflare.com' => 'Cloudflare CDN',
            'cdn.jsdelivr.net' => 'JSDelivr CDN',
            'ajax.googleapis.com' => 'Google CDN',
            'code.jquery.com' => 'jQuery CDN',
            'cdnjs.com' => 'Cloudflare CDN',
            'jsdelivr.net' => 'JSDelivr CDN',
            'unpkg.com' => 'UNPKG CDN',
        ];
        
        foreach ($cdnMap as $cdnDomain => $cdnName) {
            if (str_contains($domain, $cdnDomain)) {
                return $cdnName;
            }
        }
        
        return $domain;
    }

    /**
     * Extract optimization opportunities
     */
    private function extractOpportunities(array $audits): array
    {
        $opportunities = [];
        $opportunityKeys = [
            'render-blocking-resources',
            'uses-responsive-images',
            'offscreen-images',
            'unminified-css',
            'unminified-javascript',
            'unused-css-rules',
            'unused-javascript',
            'modern-image-formats',
            'uses-optimized-images',
            'efficient-animated-content',
        ];

        foreach ($opportunityKeys as $key) {
            if (isset($audits[$key]) && $audits[$key]['score'] !== null && $audits[$key]['score'] < 1) {
                $opportunities[$key] = [
                    'title' => $audits[$key]['title'],
                    'description' => $audits[$key]['description'],
                    'score' => $audits[$key]['score'],
                    'numericValue' => $audits[$key]['numericValue'] ?? null,
                    'displayValue' => $audits[$key]['displayValue'] ?? null,
                ];
            }
        }

        return $opportunities;
    }

    /**
     * Extract diagnostic information
     */
    private function extractDiagnostics(array $audits): array
    {
        $diagnostics = [];
        $diagnosticKeys = [
            'main-thread-work-breakdown',
            'third-party-summary',
            'dom-size',
            'long-tasks',
            'total-byte-weight',
        ];

        foreach ($diagnosticKeys as $key) {
            if (isset($audits[$key])) {
                $diagnostics[$key] = [
                    'title' => $audits[$key]['title'],
                    'description' => $audits[$key]['description'],
                    'score' => $audits[$key]['score'] ?? null,
                    'numericValue' => $audits[$key]['numericValue'] ?? null,
                    'displayValue' => $audits[$key]['displayValue'] ?? null,
                ];
            }
        }

        return $diagnostics;
    }
}
