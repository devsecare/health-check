<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PageSpeedInsightsService
{
    private $apiKey;
    private $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_PAGESPEED_API_KEY', 'AIzaSyCoVih0N2CMDPcj_QFwjF2GrWmhc1QibZA');
    }

    /**
     * Run PageSpeed Insights test for a URL
     *
     * @param string $url
     * @param string $strategy 'mobile' or 'desktop'
     * @return array|null
     */
    public function runTest(string $url, string $strategy = 'mobile')
    {
        try {
            // Build query string with multiple category parameters
            // Google API expects: category=performance&category=accessibility&category=best-practices&category=seo
            $queryParams = http_build_query([
                'url' => $url,
                'key' => $this->apiKey,
                'strategy' => $strategy,
            ]);

            // Add each category as a separate parameter
            $categories = ['performance', 'accessibility', 'best-practices', 'seo'];
            foreach ($categories as $category) {
                $queryParams .= '&category=' . urlencode($category);
            }

            // Log the request for debugging
            Log::info('PageSpeed API Request', [
                'url' => $url,
                'strategy' => $strategy,
                'categories' => $categories,
                'query_string' => $queryParams
            ]);

            // Use longer timeout for PageSpeed API (can take 30-120 seconds)
            $response = Http::timeout(180)->connectTimeout(30)->get($this->apiUrl . '?' . $queryParams);

            if ($response->successful()) {
                $data = $response->json();

                // Log what categories are actually in the response
                $responseCategories = $data['lighthouseResult']['categories'] ?? [];
                Log::info('PageSpeed API Response Categories', [
                    'requested' => $categories,
                    'received' => array_keys($responseCategories),
                    'categories' => $responseCategories
                ]);

                return $this->parseResponse($data);
            }

            // Handle timeout specifically
            if ($response->status() === 0 || $response->failed()) {
                Log::error('PageSpeed Insights API Error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'timeout' => true
                ]);

                throw new \Exception('PageSpeed API request timed out. The test may take up to 2-3 minutes. Please try again.');
            }

            Log::error('PageSpeed Insights API Error', [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            throw new \Exception('PageSpeed API returned error: ' . $response->status() . ' - ' . $response->body());
        } catch (\Exception $e) {
            Log::error('PageSpeed Insights Exception', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Parse PageSpeed Insights API response
     *
     * @param array $data
     * @return array
     */
    private function parseResponse(array $data): array
    {
        $lighthouseResult = $data['lighthouseResult'] ?? [];
        $categories = $lighthouseResult['categories'] ?? [];
        $audits = $lighthouseResult['audits'] ?? [];
        $metrics = $lighthouseResult['audits']['metrics']['details']['items'][0] ?? [];

        // Debug: Log categories to see what we're getting
        Log::info('PageSpeed Categories', ['categories' => array_keys($categories), 'data' => $categories]);

        return [
            'strategy' => $data['lighthouseResult']['configSettings']['emulatedFormFactor'] ?? 'mobile',
            'performance_score' => $this->getScore($categories, 'performance'),
            'accessibility_score' => $this->getScore($categories, 'accessibility'),
            'seo_score' => $this->getScore($categories, 'seo'),
            'best_practices_score' => $this->getScore($categories, 'best-practices'),

            // Core Web Vitals
            'lcp' => $this->getMetricValue($audits, 'largest-contentful-paint') / 1000, // Convert to seconds
            'fcp' => $this->getMetricValue($audits, 'first-contentful-paint') / 1000,
            'cls' => $this->getMetricValue($audits, 'cumulative-layout-shift'),
            'tbt' => $this->getMetricValue($audits, 'total-blocking-time') / 1000,
            'si' => $this->getMetricValue($audits, 'speed-index') / 1000,

            // Additional metrics
            'ttfb' => $this->getMetricValue($audits, 'server-response-time') / 1000,
            'interactive' => $this->getMetricValue($audits, 'interactive') / 1000,

            // Raw data for detailed analysis
            'raw_data' => json_encode($data),

            // Optimization suggestions
            'opportunities' => $this->extractOpportunities($audits),
            'diagnostics' => $this->extractDiagnostics($audits),
        ];
    }

    /**
     * Get category score
     *
     * @param array $categories
     * @param string $category
     * @return int|null
     */
    private function getScore(array $categories, string $category): ?int
    {
        // Try different possible category key names
        $possibleKeys = [$category, str_replace('_', '-', $category), str_replace('-', '_', $category)];

        foreach ($possibleKeys as $key) {
            if (isset($categories[$key]['score'])) {
                $score = $categories[$key]['score'];
                // Score can be null, so handle that case
                if ($score !== null) {
                    return (int) round($score * 100);
                }
            }
        }

        // Log what categories are actually available
        Log::warning('Category not found', [
            'looking_for' => $category,
            'available' => array_keys($categories)
        ]);

        return null;
    }

    /**
     * Get metric value from audits
     *
     * @param array $audits
     * @param string $metric
     * @return float
     */
    private function getMetricValue(array $audits, string $metric): float
    {
        return isset($audits[$metric]['numericValue'])
            ? (float) $audits[$metric]['numericValue']
            : 0;
    }

    /**
     * Extract optimization opportunities
     *
     * @param array $audits
     * @return array
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
     *
     * @param array $audits
     * @return array
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

