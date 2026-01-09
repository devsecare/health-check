@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<div class="space-y-6">
    <!-- PageSpeed Insights Overview Section -->
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">PageSpeed Insights Overview</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Performance analytics across all websites</p>
        </div>

        <!-- PageSpeed Stats Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Tests</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalTests) }}</p>
                <p class="mt-2 text-sm text-blue-600 dark:text-blue-400">{{ number_format($testsThisMonth) }} this month</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Websites Tracked</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalWebsites) }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ number_format($websitesWithTests) }} with test data</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Performance</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                @php
                    $perfScore = round($avgPerformance ?? 0);
                    $perfColor = ($perfScore >= 90) ? 'text-green-600 dark:text-green-400' : (($perfScore >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                @endphp
                <p class="text-3xl font-bold {{ $perfColor }}">{{ $perfScore }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">out of 100</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. LCP</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @php
                    $lcp = round($avgLcp ?? 0, 2);
                    $lcpColor = ($lcp <= 2.5) ? 'text-green-600 dark:text-green-400' : (($lcp <= 4) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                @endphp
                <p class="text-3xl font-bold {{ $lcpColor }}">{{ $lcp }}s</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Largest Contentful Paint</p>
            </div>
        </div>

        <!-- Average Scores Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Performance', 'value' => round($avgPerformance ?? 0), 'key' => 'performance'],
                ['label' => 'Accessibility', 'value' => round($avgAccessibility ?? 0), 'key' => 'accessibility'],
                ['label' => 'Best Practices', 'value' => round($avgBestPractices ?? 0), 'key' => 'best_practices'],
                ['label' => 'SEO', 'value' => round($avgSeo ?? 0), 'key' => 'seo']
            ] as $score)
                @php
                    $value = $score['value'];
                    // Determine colors based on score
                    if ($value >= 90) {
                        $barColorHex = '#16a34a'; // green-600
                        $barColorClass = 'bg-green-600';
                        $textColor = 'text-green-600 dark:text-green-400';
                    } elseif ($value >= 50) {
                        $barColorHex = '#ca8a04'; // yellow-600
                        $barColorClass = 'bg-yellow-600';
                        $textColor = 'text-yellow-600 dark:text-yellow-400';
                    } else {
                        $barColorHex = '#dc2626'; // red-600
                        $barColorClass = 'bg-red-600';
                        $textColor = 'text-red-600 dark:text-red-400';
                    }
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3">{{ $score['label'] }}</h4>
                    <div class="flex items-baseline mb-2">
                        <span class="text-2xl font-bold {{ $textColor }}">{{ $value }}</span>
                        <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">/ 100</span>
                    </div>
                    <div class="w-full rounded-full overflow-hidden" style="height: 8px; background-color: #e5e7eb; position: relative;">
                        @if($value > 0)
                        <div style="position: absolute; left: 0; top: 0; height: 8px; width: {{ $value }}%; background-color: {{ $barColorHex }}; border-radius: 9999px; min-width: 2px; z-index: 1;"></div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Performance Trends Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Performance Score Trends</h3>
            <div class="h-64 flex items-end justify-center space-x-2">
                @php
                    $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    $maxScore = $performanceByMonth->max(function($item) { return $item->avg_score ?? 0; }) ?: 100;
                    $currentYear = now()->year;
                    $perfChartData = collect(range(1, 12))->map(function($month) use ($performanceByMonth, $currentYear) {
                        // Find month data for current year or any year if current year has no data
                        $monthData = $performanceByMonth->firstWhere('month', $month);
                        return [
                            'month' => $month,
                            'score' => $monthData ? round($monthData->avg_score) : null,
                            'year' => $monthData ? $monthData->year : $currentYear
                        ];
                    });
                @endphp
                @foreach($perfChartData as $index => $data)
                    @if($data['score'] !== null)
                        @php
                            $height = $maxScore > 0 ? ($data['score'] / $maxScore) * 100 : 0;
                            // Ensure minimum height for visibility - at least 10% or based on score
                            $barHeight = max($height, 10);
                            // Set gradient colors with fallback
                            if ($data['score'] >= 90) {
                                $gradientColors = 'linear-gradient(to top, #16a34a, #4ade80)';
                                $barColor = 'from-green-600 to-green-400';
                            } elseif ($data['score'] >= 50) {
                                $gradientColors = 'linear-gradient(to top, #ca8a04, #eab308)';
                                $barColor = 'from-yellow-600 to-yellow-400';
                            } else {
                                $gradientColors = 'linear-gradient(to top, #dc2626, #ef4444)';
                                $barColor = 'from-red-600 to-red-400';
                            }
                        @endphp
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gradient-to-t {{ $barColor }} rounded-t-lg mb-2" style="height: {{ $barHeight }}%; min-height: 30px; background-image: {{ $gradientColors }}; background-color: {{ $data['score'] >= 90 ? '#16a34a' : ($data['score'] >= 50 ? '#ca8a04' : '#dc2626') }};"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $monthNames[$index] }}</span>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">{{ $data['score'] }}</span>
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-t-lg mb-2" style="height: 5%; min-height: 10px;"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $monthNames[$index] }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 mt-1">-</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Top Performing Websites -->
        @if($topWebsites->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Performing Websites</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Website</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg. Performance</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($topWebsites as $website)
                            @php
                                $score = round($website->avg_performance ?? 0);
                                $scoreColor = ($score >= 90) ? 'text-green-600 dark:text-green-400' : (($score >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                                $badgeColor = ($score >= 90) ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : (($score >= 50) ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $website->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $website->url }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-lg font-bold {{ $scoreColor }}">{{ $score }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">/ 100</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badgeColor }}">
                                        {{ $score >= 90 ? 'Excellent' : ($score >= 50 ? 'Good' : 'Needs Improvement') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Recent Tests -->
        @if($recentTests->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent PageSpeed Tests</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Website</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Strategy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Performance</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentTests as $test)
                            @php
                                $score = $test->performance_score ?? 0;
                                $scoreColor = ($score >= 90) ? 'text-green-600 dark:text-green-400' : (($score >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $test->website->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ ucfirst($test->strategy ?? 'mobile') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold {{ $scoreColor }}">{{ $score ?? 'N/A' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">
                                    {{ $test->created_at->format('M d, Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- SEO Audit Overview Section -->
    <div class="space-y-6 mt-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">SEO Audit Overview</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">SEO analytics across all websites</p>
        </div>

        <!-- SEO Audit Stats Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Audits</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSeoAudits ?? 0) }}</p>
                <p class="mt-2 text-sm text-blue-600 dark:text-blue-400">{{ number_format($seoAuditsThisMonth ?? 0) }} this month</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Websites Audited</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($websitesWithSeoAudits ?? 0) }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">out of {{ number_format($totalWebsites ?? 0) }} websites</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. SEO Score</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                @php
                    $seoScore = round($avgSeoAuditScore ?? 0);
                    $seoColor = ($seoScore >= 90) ? 'text-green-600 dark:text-green-400' : (($seoScore >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                @endphp
                <p class="text-3xl font-bold {{ $seoColor }}">{{ $seoScore }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">out of 100</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Coverage</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @php
                    $coverage = $totalWebsites > 0 ? round(($websitesWithSeoAudits ?? 0) / $totalWebsites * 100) : 0;
                @endphp
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $coverage }}%</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">websites with SEO data</p>
            </div>
        </div>

        <!-- SEO Score Trends Chart -->
        @php
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $maxSeoScore = ($seoAuditsByMonth ?? collect())->max(function($item) { return $item->avg_score ?? 0; }) ?: 100;
            $currentYear = now()->year;
            $seoChartData = collect(range(1, 12))->map(function($month) use ($seoAuditsByMonth, $currentYear) {
                $monthData = ($seoAuditsByMonth ?? collect())->firstWhere('month', $month);
                return [
                    'month' => $month,
                    'score' => $monthData ? round($monthData->avg_score) : null,
                    'year' => $monthData ? $monthData->year : $currentYear
                ];
            });
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">SEO Score Trends</h3>
            <div class="h-64 flex items-end justify-center space-x-2">
                @foreach($seoChartData as $index => $data)
                    @if($data['score'] !== null)
                        @php
                            $height = $maxSeoScore > 0 ? ($data['score'] / $maxSeoScore) * 100 : 0;
                            // Ensure minimum height for visibility - at least 10% or based on score
                            $barHeight = max($height, 10);
                            // Set gradient colors with fallback
                            if ($data['score'] >= 90) {
                                $gradientColors = 'linear-gradient(to top, #16a34a, #4ade80)';
                                $barColor = 'from-green-600 to-green-400';
                            } elseif ($data['score'] >= 50) {
                                $gradientColors = 'linear-gradient(to top, #ca8a04, #eab308)';
                                $barColor = 'from-yellow-600 to-yellow-400';
                            } else {
                                $gradientColors = 'linear-gradient(to top, #dc2626, #ef4444)';
                                $barColor = 'from-red-600 to-red-400';
                            }
                        @endphp
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gradient-to-t {{ $barColor }} rounded-t-lg mb-2" style="height: {{ $barHeight }}%; min-height: 30px; background-image: {{ $gradientColors }}; background-color: {{ $data['score'] >= 90 ? '#16a34a' : ($data['score'] >= 50 ? '#ca8a04' : '#dc2626') }};"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $monthNames[$index] }}</span>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">{{ $data['score'] }}</span>
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-t-lg mb-2" style="height: 5%; min-height: 10px;"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $monthNames[$index] }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 mt-1">-</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Top SEO Websites -->
        @if(($topSeoWebsites ?? collect())->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top SEO Websites</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Website</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg. SEO Score</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($topSeoWebsites as $website)
                            @php
                                $score = round($website->avg_seo_score ?? 0);
                                $scoreColor = ($score >= 90) ? 'text-green-600 dark:text-green-400' : (($score >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                                $badgeColor = ($score >= 90) ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : (($score >= 50) ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $website->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $website->url }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-lg font-bold {{ $scoreColor }}">{{ $score }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">/ 100</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badgeColor }}">
                                        {{ $score >= 90 ? 'Excellent' : ($score >= 50 ? 'Good' : 'Needs Improvement') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Recent SEO Audits -->
        @if(($recentSeoAudits ?? collect())->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent SEO Audits</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Website</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">SEO Score</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentSeoAudits as $audit)
                            @php
                                $score = $audit->overall_score ?? 0;
                                $scoreColor = ($score >= 90) ? 'text-green-600 dark:text-green-400' : (($score >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $audit->website->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold {{ $scoreColor }}">{{ $score ?? 'N/A' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">
                                    {{ $audit->created_at->format('M d, Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Broken Links Overview Section -->
    <div class="space-y-6 mt-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Broken Links Overview</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Broken links analytics across all websites</p>
        </div>

        <!-- Broken Links Stats Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Checks</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0L3 3m3.59 3.59L12 12m0 0l8.41 8.41M12 12l-8.41-8.41M12 12l8.41 8.41"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalBrokenLinksChecks ?? 0) }}</p>
                <p class="mt-2 text-sm text-blue-600 dark:text-blue-400">{{ number_format($brokenLinksChecksThisMonth ?? 0) }} this month</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Websites Checked</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($websitesWithBrokenLinksChecks ?? 0) }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">out of {{ number_format($totalWebsites ?? 0) }} websites</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Broken Links</h3>
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ number_format($totalBrokenLinksFound ?? 0) }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">across {{ number_format($totalPagesChecked ?? 0) }} pages</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Broken Links Rate</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                @php
                    $brokenRate = $totalPagesChecked > 0 ? round(($totalBrokenLinksFound ?? 0) / $totalPagesChecked * 100, 2) : 0;
                    $rateColor = ($brokenRate <= 1) ? 'text-green-600 dark:text-green-400' : (($brokenRate <= 5) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                @endphp
                <p class="text-3xl font-bold {{ $rateColor }}">{{ $brokenRate }}%</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">average broken links</p>
            </div>
        </div>

        <!-- Broken Links Trends Chart -->
        @php
            $maxBrokenLinks = ($brokenLinksByMonth ?? collect())->max(function($item) { return $item->total_broken ?? 0; }) ?: 100;
            $brokenChartData = collect(range(1, 12))->map(function($month) use ($brokenLinksByMonth, $currentYear) {
                $monthData = ($brokenLinksByMonth ?? collect())->firstWhere('month', $month);
                return [
                    'month' => $month,
                    'broken' => $monthData ? ($monthData->total_broken ?? 0) : null,
                    'checked' => $monthData ? ($monthData->total_checked ?? 0) : null,
                    'year' => $monthData ? $monthData->year : $currentYear
                ];
            });
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Broken Links Trends</h3>
            <div class="h-64 flex items-end justify-center space-x-2">
                @foreach($brokenChartData as $index => $data)
                    @if($data['broken'] !== null)
                        @php
                            $height = $maxBrokenLinks > 0 ? ($data['broken'] / $maxBrokenLinks) * 100 : 0;
                            $barColor = 'from-red-600 to-red-400';
                        @endphp
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gradient-to-t {{ $barColor }} rounded-t-lg mb-2" style="height: {{ max($height, 5) }}%"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $monthNames[$index] }}</span>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">{{ $data['broken'] }}</span>
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-t-lg mb-2" style="height: 5%"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $monthNames[$index] }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 mt-1">-</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Websites with Most Broken Links -->
        @if(($websitesWithMostBrokenLinks ?? collect())->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Websites with Most Broken Links</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Website</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Broken Links</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($websitesWithMostBrokenLinks as $website)
                            @php
                                $totalBroken = $website->total_broken_links ?? 0;
                                $badgeColor = ($totalBroken == 0) ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : (($totalBroken <= 10) ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400');
                                $status = ($totalBroken == 0) ? 'Clean' : (($totalBroken <= 10) ? 'Minor Issues' : 'Needs Attention');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $website->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $website->url }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-lg font-bold {{ $totalBroken > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ number_format($totalBroken) }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badgeColor }}">
                                        {{ $status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Recent Broken Links Checks -->
        @if(($recentBrokenLinksChecks ?? collect())->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Broken Links Checks</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Website</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pages Checked</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Broken Links</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentBrokenLinksChecks as $check)
                            @php
                                $broken = $check->total_broken ?? 0;
                                $checked = $check->total_checked ?? 0;
                                $brokenColor = ($broken > 0) ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $check->website->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($checked) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold {{ $brokenColor }}">{{ number_format($broken) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">
                                    {{ $check->created_at->format('M d, Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Domain Authority Overview Section -->
    <div class="space-y-6 mt-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Domain Authority Overview</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Domain Authority analytics and history across all websites</p>
        </div>

        <!-- Domain Authority Stats Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Checks</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalDomainAuthorityChecks ?? 0) }}</p>
                <p class="mt-2 text-sm text-blue-600 dark:text-blue-400">{{ number_format($domainAuthorityChecksThisMonth ?? 0) }} this month</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Websites Tracked</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($websitesWithDomainAuthority ?? 0) }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">out of {{ number_format($totalWebsites ?? 0) }} websites</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Domain Authority</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                @php
                    $daScore = round($avgDomainAuthority ?? 0);
                    $daColor = ($daScore >= 50) ? 'text-green-600 dark:text-green-400' : (($daScore >= 30) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                @endphp
                <p class="text-3xl font-bold {{ $daColor }}">{{ $daScore }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">out of 100</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Backlinks</h3>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalBacklinks ?? 0) }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ number_format($totalReferringDomains ?? 0) }} referring domains</p>
            </div>
        </div>

        <!-- Domain Authority History Chart -->
        @php
            $maxDA = ($domainAuthorityByMonth ?? collect())->max(function($item) { return $item->avg_domain_authority ?? 0; }) ?: 100;
            $daChartData = collect(range(1, 12))->map(function($month) use ($domainAuthorityByMonth, $currentYear) {
                $monthData = ($domainAuthorityByMonth ?? collect())->firstWhere('month', $month);
                return [
                    'month' => $month,
                    'da' => $monthData ? round($monthData->avg_domain_authority) : null,
                    'year' => $monthData ? $monthData->year : $currentYear
                ];
            });
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Domain Authority History</h3>
            <div class="h-64 flex items-end justify-center space-x-2">
                @foreach($daChartData as $index => $data)
                    @if($data['da'] !== null)
                        @php
                            $height = $maxDA > 0 ? ($data['da'] / $maxDA) * 100 : 0;
                            $barHeight = max($height, 10);
                            if ($data['da'] >= 50) {
                                $gradientColors = 'linear-gradient(to top, #16a34a, #4ade80)';
                                $barColor = 'from-green-600 to-green-400';
                            } elseif ($data['da'] >= 30) {
                                $gradientColors = 'linear-gradient(to top, #ca8a04, #eab308)';
                                $barColor = 'from-yellow-600 to-yellow-400';
                            } else {
                                $gradientColors = 'linear-gradient(to top, #dc2626, #ef4444)';
                                $barColor = 'from-red-600 to-red-400';
                            }
                        @endphp
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gradient-to-t {{ $barColor }} rounded-t-lg mb-2" style="height: {{ $barHeight }}%; min-height: 30px; background-image: {{ $gradientColors }}; background-color: {{ $data['da'] >= 50 ? '#16a34a' : ($data['da'] >= 30 ? '#ca8a04' : '#dc2626') }};"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $monthNames[$index] }}</span>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-1">{{ $data['da'] }}</span>
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-t-lg mb-2" style="height: 5%; min-height: 10px;"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $monthNames[$index] }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 mt-1">-</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Top Domain Authority Websites -->
        @if(($topDomainAuthorityWebsites ?? collect())->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Domain Authority Websites</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Website</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg. Domain Authority</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($topDomainAuthorityWebsites as $website)
                            @php
                                $da = round($website->avg_domain_authority ?? 0);
                                $daColor = ($da >= 50) ? 'text-green-600 dark:text-green-400' : (($da >= 30) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                                $badgeColor = ($da >= 50) ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : (($da >= 30) ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $website->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $website->url }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-lg font-bold {{ $daColor }}">{{ $da }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">/ 100</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badgeColor }}">
                                        {{ $da >= 50 ? 'Excellent' : ($da >= 30 ? 'Good' : 'Needs Improvement') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Recent Domain Authority Checks -->
        @if(($recentDomainAuthorityChecks ?? collect())->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Domain Authority Checks</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Website</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Domain Authority</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Page Authority</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Backlinks</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentDomainAuthorityChecks as $check)
                            @php
                                $da = $check->domain_authority ?? 0;
                                $pa = $check->page_authority ?? 0;
                                $daColor = ($da >= 50) ? 'text-green-600 dark:text-green-400' : (($da >= 30) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                                $backlinks = $check->backlinks ?? 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $check->website->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold {{ $daColor }}">{{ $da ?? 'N/A' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $pa ?? 'N/A' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($backlinks) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">
                                    {{ $check->created_at->format('M d, Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

