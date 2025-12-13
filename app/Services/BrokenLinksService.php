<?php

namespace App\Services;

use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;

class BrokenLinksService
{
    private $brokenLinks = [];
    private $visitedUrls = [];
    private $baseUrl = '';
    private $maxDepth = 2;
    private $maxPages = 30;
    private $progressCallback = null;
    private $expectedTotal = 0;
    private $currentProgress = 0;
    
    /**
     * Get current broken links (for access during execution)
     */
    public function getBrokenLinks(): array
    {
        return $this->brokenLinks;
    }

    /**
     * Set progress callback
     */
    public function setProgressCallback(callable $callback): void
    {
        $this->progressCallback = $callback;
    }

    /**
     * Update progress
     */
    private function updateProgress(int $progress, string $message = '', ?int $totalChecked = null, ?int $totalBroken = null): void
    {
        $this->currentProgress = $progress;
        if ($this->progressCallback) {
            // Pass total_checked and total_broken if provided, otherwise use current counts
            $totalChecked = $totalChecked ?? count($this->visitedUrls);
            $totalBroken = $totalBroken ?? count($this->brokenLinks);
            
            try {
                call_user_func($this->progressCallback, $progress, $message, $totalChecked, $totalBroken);
                
                // Log progress updates for debugging (only for important milestones)
                if ($progress >= 100 || $progress % 10 == 0) {
                    Log::debug('Progress update', [
                        'progress' => $progress,
                        'total_checked' => $totalChecked,
                        'total_broken' => $totalBroken,
                        'message' => $message
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Progress callback error', [
                    'progress' => $progress,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Run broken links check for a URL
     *
     * @param string $url
     * @param int $maxDepth
     * @param int $maxPages
     * @return array|null
     */
    public function runCheck(string $url, int $maxDepth = 2, int $maxPages = 30): ?array
    {
        try {
            set_time_limit(300); // 5 minutes - increased for larger sites
            ini_set('max_execution_time', 300);

            $this->baseUrl = $url;
            $this->maxDepth = $maxDepth;
            $this->maxPages = $maxPages;
            $this->brokenLinks = [];
            $this->visitedUrls = [];
            $this->expectedTotal = $maxPages;
            $this->currentProgress = 0;

            $this->updateProgress(10, 'Starting crawl...', 0, 0);

            // Create observer
            $observer = new class($this) extends CrawlObserver {
                private $service;

                public function __construct(BrokenLinksService $service)
                {
                    $this->service = $service;
                }

                public function crawled(
                    \Psr\Http\Message\UriInterface $url,
                    \Psr\Http\Message\ResponseInterface $response,
                    ?\Psr\Http\Message\UriInterface $foundOnUrl = null,
                    ?string $linkText = null
                ): void {
                    $this->service->checkPage($url->__toString(), $response);
                }

                public function crawlFailed(
                    \Psr\Http\Message\UriInterface $url,
                    RequestException $requestException,
                    ?\Psr\Http\Message\UriInterface $foundOnUrl = null,
                    ?string $linkText = null
                ): void {
                    $this->service->recordBrokenLink(
                        $url->__toString(),
                        $requestException->getCode() ?: 0,
                        $requestException->getMessage(),
                        $foundOnUrl ? $foundOnUrl->__toString() : null
                    );
                }
            };

            // Start crawling with optimized settings and timeout
            $crawlStartTime = time();
            $maxCrawlTime = 120; // Maximum 2 minutes for crawling
            
            try {
                // Use pcntl_alarm if available, otherwise rely on execution time limit
                $crawler = Crawler::create([
                    \GuzzleHttp\RequestOptions::TIMEOUT => 10,
                    \GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 5,
                    \GuzzleHttp\RequestOptions::VERIFY => false, // Allow self-signed certificates
                ])
                    ->setCrawlObserver($observer)
                    ->setMaximumDepth($maxDepth)
                    ->setTotalCrawlLimit($maxPages)
                    ->setCrawlProfile(new CrawlInternalUrls($url))
                    ->setDelayBetweenRequests(100); // 100ms delay between requests
                
                // Start crawling
                $crawler->startCrawling($url);
                
                // Check if we've exceeded max time
                if ((time() - $crawlStartTime) > $maxCrawlTime) {
                    Log::warning('Crawler exceeded maximum time', [
                        'url' => $url,
                        'elapsed_time' => time() - $crawlStartTime
                    ]);
                }
            } catch (\Exception $crawlException) {
                Log::error('Crawler Error', [
                    'url' => $url,
                    'error' => $crawlException->getMessage(),
                    'trace' => $crawlException->getTraceAsString()
                ]);
                // Still return results even if crawler fails
            }

            // Final progress update with complete counts
            $totalChecked = count($this->visitedUrls);
            $totalBroken = count($this->brokenLinks);
            
            // ALWAYS update to 100% and ensure counts are set
            // If no pages were checked, try to at least check the initial URL directly
            if ($totalChecked === 0) {
                Log::warning('No pages were checked during crawl, checking initial URL directly', [
                    'url' => $url,
                    'maxPages' => $maxPages,
                    'maxDepth' => $maxDepth
                ]);
                
                // Fallback: Check the initial URL directly
                try {
                    $response = Http::timeout(10)->get($url);
                    $statusCode = $response->status();
                    
                    if ($statusCode >= 400) {
                        $this->recordBrokenLink($url, $statusCode, 'Initial page returned error status', null);
                        $totalBroken = count($this->brokenLinks);
                    }
                    
                    // At least we checked the initial URL
                    $this->visitedUrls[] = $url;
                    $totalChecked = 1;
                    
                    Log::info('Fallback check completed', [
                        'url' => $url,
                        'total_checked' => $totalChecked,
                        'total_broken' => $totalBroken
                    ]);
                    
                    $this->updateProgress(100, 'Check completed (initial URL only)', $totalChecked, $totalBroken);
                } catch (\Exception $e) {
                    Log::error('Failed to check initial URL', [
                        'url' => $url,
                        'error' => $e->getMessage()
                    ]);
                    // Mark initial URL as broken
                    $this->recordBrokenLink($url, 0, $e->getMessage(), null);
                    $this->visitedUrls[] = $url;
                    $totalChecked = 1;
                    $totalBroken = count($this->brokenLinks);
                    
                    Log::info('Fallback check completed with error', [
                        'url' => $url,
                        'total_checked' => $totalChecked,
                        'total_broken' => $totalBroken
                    ]);
                    
                    $this->updateProgress(100, 'Check completed (initial URL failed)', $totalChecked, $totalBroken);
                }
            } else {
                Log::info('Crawl check completed', [
                    'url' => $url,
                    'total_checked' => $totalChecked,
                    'total_broken' => $totalBroken
                ]);
                $this->updateProgress(100, 'Check completed', $totalChecked, $totalBroken);
            }
            
            // Force a small delay to ensure the final update is processed
            usleep(500000); // 0.5 seconds

            // Ensure totalBroken matches the actual count of broken links
            $totalBroken = count($this->brokenLinks);
            $totalChecked = count($this->visitedUrls);

            return [
                'url' => $url,
                'broken_links' => $this->brokenLinks,
                'total_checked' => $totalChecked,
                'total_broken' => $totalBroken,
                'summary' => $this->generateSummary()
            ];

        } catch (\Exception $e) {
            Log::error('Broken Links Check Error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Check a page for broken links and resources
     */
    public function checkPage(string $url, $response): void
    {
        if (in_array($url, $this->visitedUrls)) {
            return;
        }

        $this->visitedUrls[] = $url;

        // Update progress based on visited pages
        if ($this->expectedTotal > 0) {
            // Calculate progress: 10% base + 85% for pages + 5% reserved for finalization
            $progress = min(95, 10 + (int)((count($this->visitedUrls) / $this->expectedTotal) * 85));
            $this->updateProgress($progress, 'Checking page ' . count($this->visitedUrls) . ' of ' . $this->expectedTotal, count($this->visitedUrls), count($this->brokenLinks));
        } else {
            // Update with current counts even if we don't have expected total
            // Use a more conservative estimate that doesn't cap too early
            $progress = min(90, 10 + count($this->visitedUrls) * 5); // More granular updates
            $this->updateProgress($progress, 'Checking page ' . count($this->visitedUrls), count($this->visitedUrls), count($this->brokenLinks));
        }

        // Check if the page itself is broken
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            $this->recordBrokenLink($url, $statusCode, 'Page returned error status', null);
        }

        // If not HTML, skip parsing
        $contentType = $response->getHeaderLine('Content-Type');
        if (strpos($contentType, 'text/html') === false) {
            return;
        }

        try {
            $html = (string) $response->getBody();
            $this->checkLinksInHtml($html, $url);
            $this->checkResourcesInHtml($html, $url);
        } catch (\Exception $e) {
            Log::warning('Failed to parse HTML for broken links', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check links in HTML content
     */
    private function checkLinksInHtml(string $html, string $baseUrl): void
    {
        $crawler = new \Symfony\Component\DomCrawler\Crawler($html, $baseUrl);

        // Check all links
        $crawler->filter('a[href]')->each(function ($node) use ($baseUrl) {
            $href = $node->attr('href');
            if (empty($href) || str_starts_with($href, '#')) {
                return;
            }

            $fullUrl = $this->makeAbsoluteUrl($href, $baseUrl);
            if (!empty($fullUrl)) {
                $this->validateLink($fullUrl, $baseUrl);
            }
        });
    }

    /**
     * Check resources (images, CSS, JS) in HTML
     */
    private function checkResourcesInHtml(string $html, string $baseUrl): void
    {
        $crawler = new \Symfony\Component\DomCrawler\Crawler($html, $baseUrl);

        // Check images
        $crawler->filter('img[src]')->each(function ($node) use ($baseUrl) {
            $src = $node->attr('src');
            if (!empty($src)) {
                $fullUrl = $this->makeAbsoluteUrl($src, $baseUrl);
                if (!empty($fullUrl)) {
                    $this->validateResource($fullUrl, 'image', $baseUrl);
                }
            }
        });

        // Check CSS
        $crawler->filter('link[rel="stylesheet"][href]')->each(function ($node) use ($baseUrl) {
            $href = $node->attr('href');
            if (!empty($href)) {
                $fullUrl = $this->makeAbsoluteUrl($href, $baseUrl);
                if (!empty($fullUrl)) {
                    $this->validateResource($fullUrl, 'css', $baseUrl);
                }
            }
        });

        // Check JS
        $crawler->filter('script[src]')->each(function ($node) use ($baseUrl) {
            $src = $node->attr('src');
            if (!empty($src)) {
                $fullUrl = $this->makeAbsoluteUrl($src, $baseUrl);
                if (!empty($fullUrl)) {
                    $this->validateResource($fullUrl, 'javascript', $baseUrl);
                }
            }
        });
    }

    /**
     * Validate a link
     */
    private function validateLink(string $url, ?string $foundOn = null): void
    {
        // Skip if already checked or in visited list
        if (in_array($url, $this->visitedUrls)) {
            return;
        }

        try {
            $response = Http::timeout(5)->connectTimeout(3)->head($url);
            $statusCode = $response->status();

            if ($statusCode >= 400) {
                $this->recordBrokenLink($url, $statusCode, 'Link returned error status', $foundOn);
            }
        } catch (\Exception $e) {
            $statusCode = 0;
            if ($e instanceof \Illuminate\Http\Client\ConnectionException) {
                $statusCode = 0;
            } elseif (method_exists($e, 'getCode')) {
                $statusCode = $e->getCode();
            }
            $this->recordBrokenLink($url, $statusCode, $e->getMessage(), $foundOn);
        }
    }

    /**
     * Validate a resource (image, CSS, JS)
     */
    private function validateResource(string $url, string $type, ?string $foundOn = null): void
    {
        try {
            $response = Http::timeout(5)->connectTimeout(3)->head($url);
            $statusCode = $response->status();

            if ($statusCode >= 400) {
                $this->recordBrokenLink($url, $statusCode, "{$type} resource returned error status", $foundOn, $type);
            }
        } catch (\Exception $e) {
            $statusCode = 0;
            if (method_exists($e, 'getCode')) {
                $statusCode = $e->getCode();
            }
            $this->recordBrokenLink($url, $statusCode, $e->getMessage(), $foundOn, $type);
        }
    }

    /**
     * Record a broken link
     */
    public function recordBrokenLink(string $url, int $statusCode, string $error, ?string $foundOn = null, ?string $type = 'link'): void
    {
        // Skip empty URLs
        if (empty($url) || trim($url) === '') {
            return;
        }

        // Avoid duplicates
        foreach ($this->brokenLinks as $broken) {
            if ($broken['url'] === $url) {
                return;
            }
        }

        $this->brokenLinks[] = [
            'url' => $url,
            'status_code' => $statusCode,
            'error' => $error,
            'found_on' => $foundOn,
            'type' => $type,
            'is_internal' => $this->isInternal($url),
            'is_external' => !$this->isInternal($url),
        ];
        
        // Update progress with new broken link count
        if ($this->expectedTotal > 0) {
            $progress = min(95, 10 + (int)((count($this->visitedUrls) / $this->expectedTotal) * 85));
        } else {
            $progress = min(90, 10 + count($this->visitedUrls) * 5); // More granular updates
        }
        $this->updateProgress($progress, 'Found ' . count($this->brokenLinks) . ' broken links so far', count($this->visitedUrls), count($this->brokenLinks));
    }

    /**
     * Generate summary
     */
    private function generateSummary(): array
    {
        $byType = [];
        $byStatusCode = [];
        $internal = 0;
        $external = 0;

        foreach ($this->brokenLinks as $link) {
            $type = $link['type'] ?? 'link';
            if (!isset($byType[$type])) {
                $byType[$type] = 0;
            }
            $byType[$type]++;

            $code = $link['status_code'] ?? 0;
            if (!isset($byStatusCode[$code])) {
                $byStatusCode[$code] = 0;
            }
            $byStatusCode[$code]++;

            if ($link['is_internal']) {
                $internal++;
            } else {
                $external++;
            }
        }

        return [
            'by_type' => $byType,
            'by_status_code' => $byStatusCode,
            'internal' => $internal,
            'external' => $external,
        ];
    }

    /**
     * Helper methods
     */
    private function isInternal(string $url): bool
    {
        $parsed = parse_url($this->baseUrl);
        $baseHost = $parsed['host'] ?? '';
        $urlHost = parse_url($url, PHP_URL_HOST);

        return $urlHost === $baseHost || str_ends_with($urlHost, '.' . $baseHost);
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
            return '';
        }

        // Check for duplicate domains in URL (e.g., https://domain.com/https://domain.com/path)
        $parsedBase = parse_url($baseUrl);
        $baseHost = $parsedBase['host'] ?? '';
        $baseScheme = $parsedBase['scheme'] ?? 'https';
        $baseDomainUrl = $baseScheme . '://' . $baseHost;
        
        // If URL contains duplicate domain pattern, extract the correct part
        if (!empty($baseHost) && str_contains($url, $baseDomainUrl)) {
            $parts = explode($baseDomainUrl, $url);
            if (count($parts) > 2) {
                // Multiple occurrences - take the last valid part that has content
                $lastPart = '';
                for ($i = count($parts) - 1; $i >= 0; $i--) {
                    $part = trim($parts[$i]);
                    if (!empty($part)) {
                        $lastPart = $part;
                        break;
                    }
                }
                if (!empty($lastPart)) {
                    $url = $baseDomainUrl . (str_starts_with($lastPart, '/') ? '' : '/') . ltrim($lastPart, '/');
                } else {
                    // If no valid part found, return the base domain
                    return $baseDomainUrl . '/';
                }
            } elseif (count($parts) === 2 && !str_starts_with($url, $baseDomainUrl)) {
                // Domain appears in middle, extract the part after it
                $afterDomain = trim($parts[1]);
                if (!empty($afterDomain)) {
                    $url = $baseDomainUrl . (str_starts_with($afterDomain, '/') ? '' : '/') . ltrim($afterDomain, '/');
                } else {
                    // If no valid part after domain, return base domain
                    return $baseDomainUrl . '/';
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

