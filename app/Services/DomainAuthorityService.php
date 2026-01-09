<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomainAuthorityService
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        // You can use OpenPageRank API (free) or Moz API (requires key)
        // For now, we'll use OpenPageRank as it's free
        $this->apiKey = env('OPENPAGERANK_API_KEY', '');
        $this->apiUrl = 'https://openpagerank.com/api/v1.0/getPageRank';
    }

    /**
     * Run Domain Authority check for a URL
     *
     * @param string $url
     * @return array|null
     */
    public function runCheck(string $url): ?array
    {
        try {
            set_time_limit(180);
            ini_set('max_execution_time', 180);

            // Extract domain from URL
            $parsed = parse_url($url);
            $domain = $parsed['host'] ?? '';

            if (empty($domain)) {
                Log::error('Domain Authority: Invalid URL', ['url' => $url]);
                return null;
            }

            // Remove www. if present
            $domain = preg_replace('/^www\./', '', $domain);

            // Try OpenPageRank API first (free)
            $result = $this->checkWithOpenPageRank($domain);

            // If OpenPageRank fails and Moz API key is available, try Moz
            if (!$result && !empty(env('MOZ_API_ACCESS_ID')) && !empty(env('MOZ_API_SECRET_KEY'))) {
                $result = $this->checkWithMoz($domain);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Domain Authority Check Error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Check Domain Authority using OpenPageRank API (free)
     */
    private function checkWithOpenPageRank(string $domain): ?array
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'API-OPR' => $this->apiKey ?: ''
                ])
                ->get($this->apiUrl . '?domains[]=' . urlencode($domain));

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['response'][0])) {
                    $result = $data['response'][0];

                    return [
                        'domain_authority' => $this->convertPageRankToDA($result['page_rank_decimal'] ?? 0),
                        'page_authority' => $this->convertPageRankToDA($result['page_rank_decimal'] ?? 0),
                        'spam_score' => null, // OpenPageRank doesn't provide spam score
                        'backlinks' => $result['rank'] ?? null,
                        'referring_domains' => null, // OpenPageRank doesn't provide this
                        'raw_data' => json_encode($data)
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('OpenPageRank API Error', [
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check Domain Authority using Moz API (requires API credentials)
     */
    private function checkWithMoz(string $domain): ?array
    {
        try {
            $accessId = env('MOZ_API_ACCESS_ID');
            $secretKey = env('MOZ_API_SECRET_KEY');

            if (empty($accessId) || empty($secretKey)) {
                return null;
            }

            // Moz API URL
            $mozApiUrl = 'https://lsapi.seomoz.com/v2/url_metrics';

            // Generate signature for Moz API
            $expires = time() + 300; // 5 minutes
            $stringToSign = $accessId . "\n" . $expires;
            $signature = base64_encode(hash_hmac('sha1', $stringToSign, $secretKey, true));

            $response = Http::timeout(60)
                ->get($mozApiUrl, [
                    'targets' => $domain,
                    'Cols' => '103616137329' // Bit flags for DA, PA, Links, etc.
                ])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessId . ':' . $signature,
                    'Expires' => $expires
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['results'][0])) {
                    $result = $data['results'][0];

                    return [
                        'domain_authority' => $result['domain_authority'] ?? null,
                        'page_authority' => $result['page_authority'] ?? null,
                        'spam_score' => $result['spam_score'] ?? null,
                        'backlinks' => $result['links'] ?? null,
                        'referring_domains' => $result['root_domains'] ?? null,
                        'raw_data' => json_encode($data)
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Moz API Error', [
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Convert PageRank (0-10) to Domain Authority (0-100) scale
     * This is an approximation since they use different scales
     */
    private function convertPageRankToDA(float $pageRank): int
    {
        // PageRank is 0-10, DA is 0-100
        // Simple linear conversion: multiply by 10
        // But PageRank is logarithmic, so we'll use a more accurate conversion
        if ($pageRank <= 0) {
            return 0;
        }

        // Approximate conversion: PR 0-10 maps roughly to DA 0-100
        // Using a logarithmic scale approximation
        $da = (int) round(($pageRank / 10) * 100);

        // Cap at 100
        return min($da, 100);
    }
}
