<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SeoAuditService
{
    /**
     * Run SEO audit for a URL
     *
     * @param string $url
     * @return array|null
     */
    public function runAudit(string $url): ?array
    {
        try {
            set_time_limit(180);
            ini_set('max_execution_time', 180);

            // Fetch the page HTML
            $response = Http::timeout(60)->get($url);
            
            if (!$response->successful()) {
                Log::error('SEO Audit: Failed to fetch URL', [
                    'url' => $url,
                    'status' => $response->status()
                ]);
                return null;
            }

            $html = $response->body();
            $crawler = new Crawler($html, $url);

            $results = [
                'url' => $url,
                'meta_tags' => $this->analyzeMetaTags($crawler),
                'headings' => $this->analyzeHeadings($crawler),
                'images' => $this->analyzeImages($crawler, $url),
                'url_structure' => $this->analyzeUrlStructure($url),
                'internal_links' => $this->analyzeInternalLinks($crawler, $url),
                'schema_markup' => $this->detectSchemaMarkup($crawler),
                'open_graph' => $this->analyzeOpenGraph($crawler),
                'robots_txt' => $this->checkRobotsTxt($url),
                'sitemap' => $this->checkSitemap($url),
            ];

            return $results;

        } catch (\Exception $e) {
            Log::error('SEO Audit Error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Analyze meta tags
     */
    private function analyzeMetaTags(Crawler $crawler): array
    {
        $meta = [];
        
        // Title tag
        $titleNode = $crawler->filter('title')->first();
        $meta['title'] = [
            'exists' => $titleNode->count() > 0,
            'content' => $titleNode->count() > 0 ? trim($titleNode->text()) : null,
            'length' => $titleNode->count() > 0 ? strlen(trim($titleNode->text())) : 0,
            'status' => $titleNode->count() > 0 ? ($this->isTitleOptimal(trim($titleNode->text())) ? 'good' : 'warning') : 'error'
        ];

        // Meta description
        $descriptionNode = $crawler->filter('meta[name="description"]')->first();
        $meta['description'] = [
            'exists' => $descriptionNode->count() > 0,
            'content' => $descriptionNode->count() > 0 ? $descriptionNode->attr('content') : null,
            'length' => $descriptionNode->count() > 0 ? strlen($descriptionNode->attr('content')) : 0,
            'status' => $descriptionNode->count() > 0 ? ($this->isDescriptionOptimal($descriptionNode->attr('content')) ? 'good' : 'warning') : 'error'
        ];

        // Meta keywords
        $keywordsNode = $crawler->filter('meta[name="keywords"]')->first();
        $meta['keywords'] = [
            'exists' => $keywordsNode->count() > 0,
            'content' => $keywordsNode->count() > 0 ? $keywordsNode->attr('content') : null,
        ];

        // Viewport
        $viewportNode = $crawler->filter('meta[name="viewport"]')->first();
        $meta['viewport'] = [
            'exists' => $viewportNode->count() > 0,
            'content' => $viewportNode->count() > 0 ? $viewportNode->attr('content') : null,
        ];

        // Charset
        $charsetNode = $crawler->filter('meta[charset]')->first();
        $meta['charset'] = [
            'exists' => $charsetNode->count() > 0,
            'content' => $charsetNode->count() > 0 ? $charsetNode->attr('charset') : null,
        ];

        return $meta;
    }

    /**
     * Analyze heading structure
     */
    private function analyzeHeadings(Crawler $crawler): array
    {
        $headings = [];
        
        for ($i = 1; $i <= 6; $i++) {
            $hNodes = $crawler->filter("h{$i}");
            $headings["h{$i}"] = [
                'count' => $hNodes->count(),
                'texts' => $hNodes->each(function (Crawler $node) {
                    return trim($node->text());
                })
            ];
        }

        // Check if H1 exists and count
        $h1Count = $headings['h1']['count'];
        $headings['h1_status'] = [
            'has_one' => $h1Count === 1,
            'has_none' => $h1Count === 0,
            'has_multiple' => $h1Count > 1,
            'status' => $h1Count === 1 ? 'good' : ($h1Count === 0 ? 'error' : 'warning')
        ];

        // Check heading hierarchy
        $headings['hierarchy'] = $this->checkHeadingHierarchy($headings);

        return $headings;
    }

    /**
     * Analyze images and alt attributes
     */
    private function analyzeImages(Crawler $crawler, string $baseUrl): array
    {
        $images = $crawler->filter('img')->each(function (Crawler $node) use ($baseUrl) {
            $src = $node->attr('src');
            $alt = $node->attr('alt');
            $fullUrl = $this->makeAbsoluteUrl($src, $baseUrl);

            return [
                'src' => $fullUrl,
                'alt' => $alt ?? '',
                'has_alt' => !empty($alt),
                'status' => !empty($alt) ? 'good' : 'warning'
            ];
        });

        $withAlt = collect($images)->filter(function ($img) {
            return $img['has_alt'];
        })->count();

        return [
            'total' => count($images),
            'with_alt' => $withAlt,
            'without_alt' => count($images) - $withAlt,
            'images' => $images,
            'status' => count($images) === 0 ? 'good' : (count($images) === $withAlt ? 'good' : 'warning')
        ];
    }

    /**
     * Analyze URL structure
     */
    private function analyzeUrlStructure(string $url): array
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';
        $query = $parsed['query'] ?? null;

        $issues = [];
        $status = 'good';

        // Check URL length
        $urlLength = strlen($url);
        if ($urlLength > 115) {
            $issues[] = 'URL is too long (over 115 characters)';
            $status = 'warning';
        }

        // Check for query parameters
        if ($query) {
            $issues[] = 'URL contains query parameters';
            $status = 'warning';
        }

        // Check for special characters
        if (preg_match('/[^a-zA-Z0-9\-_\/\.]/', $path)) {
            $issues[] = 'URL contains special characters';
            $status = 'warning';
        }

        // Check for underscores
        if (strpos($path, '_') !== false) {
            $issues[] = 'URL contains underscores (use hyphens instead)';
            $status = 'warning';
        }

        // Check URL depth
        $depth = count(array_filter(explode('/', $path)));
        if ($depth > 4) {
            $issues[] = 'URL is too deep (more than 4 levels)';
            $status = 'warning';
        }

        return [
            'url' => $url,
            'path' => $path,
            'length' => $urlLength,
            'depth' => $depth,
            'has_query' => !empty($query),
            'issues' => $issues,
            'status' => $status
        ];
    }

    /**
     * Analyze internal links
     */
    private function analyzeInternalLinks(Crawler $crawler, string $baseUrl): array
    {
        $parsedBase = parse_url($baseUrl);
        $baseDomain = $parsedBase['host'] ?? '';

        $links = $crawler->filter('a[href]')->each(function (Crawler $node) use ($baseUrl, $baseDomain) {
            $href = $node->attr('href');
            $fullUrl = $this->makeAbsoluteUrl($href, $baseUrl);
            $parsed = parse_url($fullUrl);
            $domain = $parsed['host'] ?? '';

            return [
                'href' => $href,
                'full_url' => $fullUrl,
                'text' => trim($node->text()),
                'is_internal' => $domain === $baseDomain || empty($domain) || str_starts_with($href, '#'),
                'is_external' => !empty($domain) && $domain !== $baseDomain && !str_starts_with($href, '#'),
                'has_text' => !empty(trim($node->text())),
                'is_anchor' => str_starts_with($href, '#'),
            ];
        });

        $internal = collect($links)->filter(function ($link) {
            return $link['is_internal'] && !$link['is_anchor'];
        })->count();

        $external = collect($links)->filter(function ($link) {
            return $link['is_external'];
        })->count();

        $noFollow = $crawler->filter('a[rel*="nofollow"]')->count();

        return [
            'total' => count($links),
            'internal' => $internal,
            'external' => $external,
            'nofollow' => $noFollow,
            'links' => array_slice($links, 0, 50), // Limit to first 50 for storage
            'status' => 'good'
        ];
    }

    /**
     * Detect schema markup
     */
    private function detectSchemaMarkup(Crawler $crawler): array
    {
        $schemas = [];

        // Check for JSON-LD
        $jsonLdNodes = $crawler->filter('script[type="application/ld+json"]');
        $jsonLdCount = $jsonLdNodes->count();
        
        if ($jsonLdCount > 0) {
            $schemas['json_ld'] = [
                'exists' => true,
                'count' => $jsonLdCount,
                'types' => $jsonLdNodes->each(function (Crawler $node) {
                    $json = json_decode($node->text(), true);
                    if (is_array($json)) {
                        return $json['@type'] ?? 'Unknown';
                    }
                    if (isset($json[0]) && is_array($json[0])) {
                        return array_map(function ($item) {
                            return $item['@type'] ?? 'Unknown';
                        }, $json);
                    }
                    return 'Unknown';
                })
            ];
        } else {
            $schemas['json_ld'] = ['exists' => false, 'count' => 0];
        }

        // Check for microdata
        $microdataNodes = $crawler->filter('[itemtype]');
        $schemas['microdata'] = [
            'exists' => $microdataNodes->count() > 0,
            'count' => $microdataNodes->count(),
            'types' => $microdataNodes->each(function (Crawler $node) {
                return $node->attr('itemtype');
            })
        ];

        // Check for RDFa
        $rdfaNodes = $crawler->filter('[typeof]');
        $schemas['rdfa'] = [
            'exists' => $rdfaNodes->count() > 0,
            'count' => $rdfaNodes->count()
        ];

        $hasAnySchema = $schemas['json_ld']['exists'] || $schemas['microdata']['exists'] || $schemas['rdfa']['exists'];

        return [
            'has_schema' => $hasAnySchema,
            'json_ld' => $schemas['json_ld'],
            'microdata' => $schemas['microdata'],
            'rdfa' => $schemas['rdfa'],
            'status' => $hasAnySchema ? 'good' : 'warning'
        ];
    }

    /**
     * Analyze Open Graph tags
     */
    private function analyzeOpenGraph(Crawler $crawler): array
    {
        $ogTags = [];
        $requiredTags = ['og:title', 'og:description', 'og:image', 'og:url', 'og:type'];

        foreach ($requiredTags as $tag) {
            $property = str_replace('og:', '', $tag);
            $nodes = $crawler->filter("meta[property=\"{$tag}\"]");
            $ogTags[$property] = [
                'exists' => $nodes->count() > 0,
                'content' => $nodes->count() > 0 ? $nodes->first()->attr('content') : null
            ];
        }

        $found = collect($ogTags)->filter(function ($tag) {
            return $tag['exists'];
        })->count();

        return [
            'tags' => $ogTags,
            'found' => $found,
            'total_required' => count($requiredTags),
            'status' => $found >= 3 ? 'good' : ($found > 0 ? 'warning' : 'error')
        ];
    }

    /**
     * Check robots.txt
     */
    private function checkRobotsTxt(string $url): array
    {
        try {
            $parsed = parse_url($url);
            $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];
            $robotsUrl = $baseUrl . '/robots.txt';

            $response = Http::timeout(10)->get($robotsUrl);

            if ($response->successful()) {
                $content = $response->body();
                return [
                    'exists' => true,
                    'accessible' => true,
                    'content' => $content,
                    'size' => strlen($content),
                    'status' => 'good'
                ];
            }

            return [
                'exists' => false,
                'accessible' => false,
                'status' => 'warning'
            ];
        } catch (\Exception $e) {
            return [
                'exists' => false,
                'accessible' => false,
                'error' => $e->getMessage(),
                'status' => 'warning'
            ];
        }
    }

    /**
     * Check XML sitemap
     */
    private function checkSitemap(string $url): array
    {
        try {
            $parsed = parse_url($url);
            $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

            $sitemapUrls = [
                $baseUrl . '/sitemap.xml',
                $baseUrl . '/sitemap_index.xml',
            ];

            foreach ($sitemapUrls as $sitemapUrl) {
                $response = Http::timeout(10)->get($sitemapUrl);
                if ($response->successful()) {
                    $content = $response->body();
                    return [
                        'exists' => true,
                        'accessible' => true,
                        'url' => $sitemapUrl,
                        'size' => strlen($content),
                        'status' => 'good'
                    ];
                }
            }

            // Check robots.txt for sitemap reference
            $robotsCheck = $this->checkRobotsTxt($url);
            if ($robotsCheck['exists'] && strpos(strtolower($robotsCheck['content']), 'sitemap:') !== false) {
                return [
                    'exists' => true,
                    'accessible' => false,
                    'referenced_in_robots' => true,
                    'status' => 'warning'
                ];
            }

            return [
                'exists' => false,
                'accessible' => false,
                'status' => 'warning'
            ];
        } catch (\Exception $e) {
            return [
                'exists' => false,
                'accessible' => false,
                'error' => $e->getMessage(),
                'status' => 'warning'
            ];
        }
    }

    /**
     * Helper methods
     */
    private function isTitleOptimal(?string $title): bool
    {
        if (!$title) return false;
        $length = strlen($title);
        return $length >= 30 && $length <= 60;
    }

    private function isDescriptionOptimal(?string $description): bool
    {
        if (!$description) return false;
        $length = strlen($description);
        return $length >= 120 && $length <= 160;
    }

    private function checkHeadingHierarchy(array $headings): array
    {
        $issues = [];
        
        // H1 should come before H2
        if ($headings['h2']['count'] > 0 && $headings['h1']['count'] === 0) {
            $issues[] = 'H2 found without H1';
        }

        // Should have reasonable heading structure
        if ($headings['h1']['count'] > 1) {
            $issues[] = 'Multiple H1 tags found';
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'status' => empty($issues) ? 'good' : 'warning'
        ];
    }

    private function makeAbsoluteUrl(?string $url, string $baseUrl): string
    {
        if (empty($url)) {
            return '';
        }

        // Trim whitespace
        $url = trim($url);

        // Anchor link
        if (str_starts_with($url, '#')) {
            return $baseUrl . $url;
        }

        // Check for duplicate domains in URL (e.g., https://domain.com/https://domain.com/path)
        $parsedBase = parse_url($baseUrl);
        $baseHost = $parsedBase['host'] ?? '';
        $baseScheme = $parsedBase['scheme'] ?? 'https';
        $baseDomainUrl = $baseScheme . '://' . $baseHost;
        
        // If URL contains duplicate domain pattern, extract the correct part
        if (!empty($baseHost) && str_contains($url, $baseDomainUrl)) {
            // Count how many times the domain appears
            $domainCount = substr_count($url, $baseDomainUrl);
            
            if ($domainCount > 1) {
                // Multiple occurrences - find the last occurrence and extract everything after it
                $lastPos = strrpos($url, $baseDomainUrl);
                if ($lastPos !== false) {
                    $afterLastDomain = substr($url, $lastPos + strlen($baseDomainUrl));
                    if (!empty($afterLastDomain)) {
                        $url = $baseDomainUrl . ($afterLastDomain[0] === '/' ? '' : '/') . ltrim($afterLastDomain, '/');
                    } else {
                        // If nothing after last domain, return base domain
                        $url = $baseDomainUrl . '/';
                    }
                }
            } elseif ($domainCount === 1 && !str_starts_with($url, $baseDomainUrl)) {
                // Domain appears in middle, extract the part after it
                $parts = explode($baseDomainUrl, $url);
                if (count($parts) === 2) {
                    $afterDomain = trim($parts[1]);
                    if (!empty($afterDomain)) {
                        $url = $baseDomainUrl . (str_starts_with($afterDomain, '/') ? '' : '/') . ltrim($afterDomain, '/');
                    } else {
                        $url = $baseDomainUrl . '/';
                    }
                }
            }
        }

        // Check if URL is already absolute (starts with http:// or https://)
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            // Parse both URLs to check if they have the same domain
            $parsedUrl = parse_url($url);
            
            $urlHost = $parsedUrl['host'] ?? '';
            
            // If URL already contains the base domain, return as-is (avoid duplication)
            if (!empty($urlHost) && ($urlHost === $baseHost || str_ends_with($urlHost, '.' . $baseHost))) {
                return $url;
            }
            
            // If it's a different domain, return as-is (it's an external absolute URL)
            return $url;
        }

        // Also check if URL already contains the base domain (for malformed URLs)
        if (!empty($baseHost) && (str_contains($url, $baseHost) || str_contains($url, $baseScheme . '://' . $baseHost))) {
            // URL already contains domain, check if it's properly formatted
            if (str_starts_with($url, $baseScheme . '://' . $baseHost)) {
                return $url;
            }
            // If it's malformed (has domain but not at start), try to extract the correct part
            $domainPos = strpos($url, $baseHost);
            if ($domainPos !== false && $domainPos > 0) {
                // This is a malformed URL, extract the part after the domain
                $afterDomain = substr($url, $domainPos + strlen($baseHost));
                if (str_starts_with($afterDomain, '/')) {
                    return $baseScheme . '://' . $baseHost . $afterDomain;
                }
            }
        }

        $parsed = parse_url($baseUrl);
        $base = $parsed['scheme'] . '://' . $parsed['host'];
        
        if (str_starts_with($url, '/')) {
            return $base . $url;
        }

        $path = dirname($parsed['path'] ?? '/');
        if ($path === '.') {
            $path = '/';
        }
        
        return $base . rtrim($path, '/') . '/' . ltrim($url, '/');
    }
}

