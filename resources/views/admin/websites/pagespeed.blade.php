@extends('layouts.admin')

@section('title', 'PageSpeed Insights - ' . $website->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">PageSpeed Insights</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $website->name }} - {{ $website->url }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.websites.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Back to Websites
            </a>
        </div>
    </div>

    <!-- Strategy Toggle -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Testing Strategy:</span>
            <div class="flex space-x-2">
                <a href="{{ route('admin.websites.pagespeed', ['website' => $website->id, 'strategy' => 'mobile']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $strategy === 'mobile' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Mobile
                </a>
                <a href="{{ route('admin.websites.pagespeed', ['website' => $website->id, 'strategy' => 'desktop']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $strategy === 'desktop' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Desktop
                </a>
            </div>
        </div>
    </div>

    @if(!$latestInsight)
    <!-- No Results - Run First Test -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex flex-col items-center justify-center py-20 px-8">
            <!-- Icon with Background -->
            <div class="relative mb-8">
                <div class="absolute inset-0 bg-blue-100 dark:bg-blue-900/20 rounded-full blur-xl"></div>
                <div class="relative w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center shadow-lg">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>

            <!-- Text Content -->
            <div class="text-center mb-10 max-w-lg">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">No PageSpeed test results</h3>
                <p class="text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                    Get started by running a PageSpeed Insights test for this website. The test will analyze:
                </p>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Performance
                    </div>
                    <div class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Accessibility
                    </div>
                    <div class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        SEO
                    </div>
                    <div class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Best Practices
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <form id="run-first-test-form" action="{{ route('admin.websites.pagespeed.run', $website) }}" method="POST" class="w-full max-w-sm space-y-4">
                @csrf
                <input type="hidden" name="strategy" value="{{ $strategy }}">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="send_email" value="1" id="send_email_first" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <label for="send_email_first" class="text-sm text-gray-700 dark:text-gray-300">Send email report when complete</label>
                </div>
                <button type="submit" id="run-first-test-btn" class="group w-full px-8 py-4 rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-[1.02] transition-all duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none" style="background-color: #2563eb; border: none; cursor: pointer;">
                    <span class="flex items-center justify-center" style="color: #ffffff !important;">
                        <svg id="run-first-icon" class="w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ffffff !important; stroke: #ffffff;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <svg id="run-first-spinner" class="hidden w-6 h-6 mr-3 animate-spin" fill="none" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="run-first-text" class="text-lg font-bold" style="color: #ffffff !important; letter-spacing: 0.025em;">Run PageSpeed Test</span>
                    </span>
                </button>
            </form>

            <!-- Info Note -->
            <div class="mt-8 flex items-center justify-center space-x-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 px-4 py-2 rounded-lg">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Tests typically take 30-60 seconds to complete</span>
            </div>
        </div>
    </div>
    @else
    <!-- Run New Test Button -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form id="run-test-form" action="{{ route('admin.websites.pagespeed.run', $website) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="strategy" value="{{ $strategy }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Last tested: {{ $latestInsight->created_at->diffForHumans() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Run a new test to get updated results</p>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="send_email" value="1" id="send_email_new" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <label for="send_email_new" class="text-sm text-gray-700 dark:text-gray-300">Send email report</label>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" id="run-test-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg id="run-test-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg id="run-test-spinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span id="run-test-text">Run New Test</span>
            </button>
        </form>
    </div>

    <!-- Scores Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Performance Score -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Performance</span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="flex items-baseline">
                @php
                    $perfScore = is_numeric($latestInsight->performance_score ?? null) ? (int)$latestInsight->performance_score : null;
                    if ($perfScore !== null) {
                        $scoreColor = ($perfScore >= 90) ? 'text-green-600 dark:text-green-400' : (($perfScore >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                        $barColorHex = ($perfScore >= 90) ? '#16a34a' : (($perfScore >= 50) ? '#ca8a04' : '#dc2626');
                    } else {
                        $scoreColor = 'text-gray-900 dark:text-white';
                        $barColorHex = '#9ca3af';
                        $perfScore = 0;
                    }
                @endphp
                <span class="text-3xl font-bold {{ $scoreColor }}">{{ $latestInsight->performance_score ?? 'N/A' }}</span>
                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">/ 100</span>
            </div>
            <div class="mt-4">
                <div class="w-full rounded-full overflow-hidden" style="height: 8px; background-color: #e5e7eb; position: relative;">
                    @if($perfScore > 0)
                    <div style="position: absolute; left: 0; top: 0; height: 8px; width: {{ $perfScore }}%; background-color: {{ $barColorHex }}; border-radius: 9999px; min-width: 2px; z-index: 1;"></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Accessibility Score -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Accessibility</span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex items-baseline">
                @php
                    $accScore = is_numeric($latestInsight->accessibility_score ?? null) ? (int)$latestInsight->accessibility_score : null;
                    if ($accScore !== null) {
                        $scoreColor = ($accScore >= 90) ? 'text-green-600 dark:text-green-400' : (($accScore >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                        $barColorHex = ($accScore >= 90) ? '#16a34a' : (($accScore >= 50) ? '#ca8a04' : '#dc2626');
                    } else {
                        $scoreColor = 'text-gray-900 dark:text-white';
                        $barColorHex = '#9ca3af';
                        $accScore = 0;
                    }
                @endphp
                <span class="text-3xl font-bold {{ $scoreColor }}">{{ $latestInsight->accessibility_score ?? 'N/A' }}</span>
                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">/ 100</span>
            </div>
            <div class="mt-4">
                <div class="w-full rounded-full overflow-hidden" style="height: 8px; background-color: #e5e7eb; position: relative;">
                    @if($accScore > 0)
                    <div style="position: absolute; left: 0; top: 0; height: 8px; width: {{ $accScore }}%; background-color: {{ $barColorHex }}; border-radius: 9999px; min-width: 2px; z-index: 1;"></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Best Practices Score -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Best Practices</span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex items-baseline">
                @php
                    $bpScore = is_numeric($latestInsight->best_practices_score ?? null) ? (int)$latestInsight->best_practices_score : null;
                    if ($bpScore !== null) {
                        $scoreColor = ($bpScore >= 90) ? 'text-green-600 dark:text-green-400' : (($bpScore >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                        $barColorHex = ($bpScore >= 90) ? '#16a34a' : (($bpScore >= 50) ? '#ca8a04' : '#dc2626');
                    } else {
                        $scoreColor = 'text-gray-900 dark:text-white';
                        $barColorHex = '#9ca3af';
                        $bpScore = 0;
                    }
                @endphp
                <span class="text-3xl font-bold {{ $scoreColor }}">{{ $latestInsight->best_practices_score ?? 'N/A' }}</span>
                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">/ 100</span>
            </div>
            <div class="mt-4">
                <div class="w-full rounded-full overflow-hidden" style="height: 8px; background-color: #e5e7eb; position: relative;">
                    @if($bpScore > 0)
                    <div style="position: absolute; left: 0; top: 0; height: 8px; width: {{ $bpScore }}%; background-color: {{ $barColorHex }}; border-radius: 9999px; min-width: 2px; z-index: 1;"></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- SEO Score -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">SEO</span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <div class="flex items-baseline">
                @php
                    $seoScore = is_numeric($latestInsight->seo_score ?? null) ? (int)$latestInsight->seo_score : null;
                    if ($seoScore !== null) {
                        $scoreColor = ($seoScore >= 90) ? 'text-green-600 dark:text-green-400' : (($seoScore >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                        $barColorHex = ($seoScore >= 90) ? '#16a34a' : (($seoScore >= 50) ? '#ca8a04' : '#dc2626');
                    } else {
                        $scoreColor = 'text-gray-900 dark:text-white';
                        $barColorHex = '#9ca3af';
                        $seoScore = 0;
                    }
                @endphp
                <span class="text-3xl font-bold {{ $scoreColor }}">{{ $latestInsight->seo_score ?? 'N/A' }}</span>
                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">/ 100</span>
            </div>
            <div class="mt-4">
                <div class="w-full rounded-full overflow-hidden" style="height: 8px; background-color: #e5e7eb; position: relative;">
                    @if($seoScore > 0)
                    <div style="position: absolute; left: 0; top: 0; height: 8px; width: {{ $seoScore }}%; background-color: {{ $barColorHex }}; border-radius: 9999px; min-width: 2px; z-index: 1;"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Screenshot Section -->
    @php
        $screenshots = $latestInsight->screenshots ?? null;
    @endphp
    @if($screenshots && isset($screenshots['final']))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Screenshot</h2>

        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900/50">
            <div class="p-4 flex justify-center">
                <div class="relative max-w-full">
                    <img src="{{ $screenshots['final'] }}"
                         alt="Screenshot of {{ $website->name }}"
                         class="max-w-full h-auto rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-pointer hover:opacity-90 transition-opacity"
                         onclick="openScreenshotModal('{{ $screenshots['final'] }}', '')"
                         title="Click to view larger"
                         style="max-height: 600px;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display: none; padding: 2rem; text-align: center; color: #6b7280;" class="screenshot-error">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm">Screenshot not available</p>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
            Screenshot captured at {{ $strategy === 'mobile' ? 'mobile' : 'desktop' }} viewport
        </p>
    </div>
    @endif

    <!-- Screenshot Modal -->
    <div id="screenshot-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-75 flex items-center justify-center p-4" onclick="closeScreenshotModal()">
        <div class="relative max-w-7xl w-full" onclick="event.stopPropagation()">
            <button onclick="closeScreenshotModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 transition-colors z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img id="modal-screenshot-img" src="" alt="Screenshot" class="w-full h-auto rounded-lg shadow-2xl max-h-[90vh] mx-auto">
            <div id="modal-screenshot-time" class="text-center text-white mt-4 text-sm"></div>
        </div>
    </div>

    <!-- Core Web Vitals -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Core Web Vitals</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">LCP</span>
                    <span class="text-xs px-2 py-1 rounded {{ $latestInsight->lcp <= 2.5 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($latestInsight->lcp <= 4 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                        {{ $latestInsight->lcp <= 2.5 ? 'Good' : ($latestInsight->lcp <= 4 ? 'Needs Improvement' : 'Poor') }}
                    </span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($latestInsight->lcp, 2) }}s</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Largest Contentful Paint</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">FCP</span>
                    <span class="text-xs px-2 py-1 rounded {{ $latestInsight->fcp <= 1.8 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($latestInsight->fcp <= 3 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                        {{ $latestInsight->fcp <= 1.8 ? 'Good' : ($latestInsight->fcp <= 3 ? 'Needs Improvement' : 'Poor') }}
                    </span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($latestInsight->fcp, 2) }}s</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">First Contentful Paint</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">CLS</span>
                    <span class="text-xs px-2 py-1 rounded {{ $latestInsight->cls <= 0.1 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($latestInsight->cls <= 0.25 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                        {{ $latestInsight->cls <= 0.1 ? 'Good' : ($latestInsight->cls <= 0.25 ? 'Needs Improvement' : 'Poor') }}
                    </span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($latestInsight->cls, 3) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cumulative Layout Shift</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">TBT</span>
                    <span class="text-xs px-2 py-1 rounded {{ $latestInsight->tbt <= 200 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($latestInsight->tbt <= 600 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                        {{ $latestInsight->tbt <= 200 ? 'Good' : ($latestInsight->tbt <= 600 ? 'Needs Improvement' : 'Poor') }}
                    </span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($latestInsight->tbt, 0) }}ms</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Blocking Time</p>
            </div>
        </div>
    </div>

    <!-- Insights Section -->
    @php
        $insightsWithResources = $latestInsight->getAllInsightsWithResources();
    @endphp
    @if(!empty($insightsWithResources))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">INSIGHTS</h2>

        <div class="space-y-4">
            @foreach($insightsWithResources as $index => $insight)
                @if($insight['hasResources'] && !empty($insight['items']))
                    @php
                        $insightId = 'insight-' . str_replace(['-', '_'], '', $insight['key']) . '-' . $index;
                        $isRenderBlocking = $insight['key'] === 'render-blocking-resources';

                        // Calculate savings text - prefer displayValue if available (matches Google's format)
                        if ($isRenderBlocking) {
                            // For render-blocking, displayValue might be like "2,620 ms" or we use wastedMs
                            if (!empty($insight['displayValue'])) {
                                $savingsText = $insight['displayValue'];
                            } else {
                                $totalSavings = $insight['wastedMs'] ?? 0;
                                $savingsText = $totalSavings > 0 ? number_format($totalSavings) . ' ms' : '';
                            }
                        } else {
                            // For others, try displayValue first (Google's format like "Est savings of 1,985 KiB")
                            if (!empty($insight['displayValue'])) {
                                // Extract just the number and unit from displayValue if it contains "Est savings of"
                                $displayValue = $insight['displayValue'];
                                if (preg_match('/\d+[\d,]*\.?\d*\s*(KiB|MiB|bytes?|KB|MB)/i', $displayValue, $matches)) {
                                    $savingsText = trim($matches[0]);
                                } else {
                                    $savingsText = $displayValue;
                                }
                            } else {
                                // Fallback: Sum up wastedBytes from all items
                                $totalWastedBytes = 0;
                                foreach ($insight['items'] as $item) {
                                    $totalWastedBytes += ($item['wastedBytes'] ?? 0);
                                }
                                // Use calculated total or fallback to insight-level wastedBytes
                                $totalWastedBytes = $totalWastedBytes > 0 ? $totalWastedBytes : ($insight['wastedBytes'] ?? 0);

                                if ($totalWastedBytes >= 1024 * 1024) {
                                    $savingsText = number_format($totalWastedBytes / (1024 * 1024), 1) . ' MiB';
                                } elseif ($totalWastedBytes >= 1024) {
                                    $savingsText = number_format($totalWastedBytes / 1024, 0) . ' KiB';
                                } elseif ($totalWastedBytes > 0) {
                                    $savingsText = number_format($totalWastedBytes) . ' bytes';
                                } else {
                                    $savingsText = '';
                                }
                            }
                        }
                        $hasWastedBytes = !$isRenderBlocking && ($insight['wastedBytes'] > 0 || isset($insight['items']));
                    @endphp

                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <button onclick="toggleInsight('{{ $insightId }}')" class="w-full px-6 py-4 text-left bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $insight['title'] }}</span>
                                    @if(!empty($savingsText))
                                        <span class="text-sm text-red-600 dark:text-red-400">— Est savings of {{ $savingsText }}</span>
                                    @endif
                                </div>
                                <svg id="{{ $insightId }}-arrow" class="w-5 h-5 text-gray-500 dark:text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        <div id="{{ $insightId }}-content" class="hidden border-t border-gray-200 dark:border-gray-700 {{ $isRenderBlocking ? 'expanded-by-default' : '' }}">
                            <div class="p-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {{ $insight['description'] }}
                                </p>

                                <!-- Third Party Toggle -->
                                @php
                                    $thirdPartyCount = 0;
                                    foreach($insight['items'] as $item) {
                                        $url = $item['url'] ?? '';
                                        $domain = parse_url($url, PHP_URL_HOST) ?? '';
                                        $websiteDomain = parse_url($website->url, PHP_URL_HOST) ?? '';
                                        if ($domain !== $websiteDomain && !str_ends_with($domain, '.' . $websiteDomain)) {
                                            $thirdPartyCount++;
                                        }
                                    }
                                @endphp
                                @if($thirdPartyCount > 0)
                                <div class="mb-4">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" id="show-third-party-{{ $index }}" checked class="show-third-party-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600" onchange="toggleThirdPartyResources('{{ $index }}')" data-insight-id="{{ $insightId }}">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Show 3rd-party resources ({{ $thirdPartyCount }})</span>
                                    </label>
                                </div>
                                @endif

                                <!-- Resources Table -->
                                @php
                                    $grouped = $latestInsight->groupResourcesByDomain($insight['items'], $website->url, $insight['key']);
                                @endphp
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">URL</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transfer Size</th>
                                                @if($isRenderBlocking)
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                                                @elseif($hasWastedBytes)
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Potential Savings</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($grouped as $groupKey => $group)
                                                <!-- Group Header -->
                                                <tr class="bg-blue-50 dark:bg-blue-900/20">
                                                    <td class="px-4 py-3">
                                                        <span class="text-sm font-medium text-blue-900 dark:text-blue-300">
                                                            @if($group['isFirstParty'])
                                                                1st Party
                                                            @else
                                                                {{ $groupKey }}
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <span class="text-sm font-medium text-blue-900 dark:text-blue-300">{{ number_format($group['totalSize'] / 1024, 1) }} KiB</span>
                                                    </td>
                                                    @if($isRenderBlocking)
                                                        <td class="px-4 py-3 text-right">
                                                            <span class="text-sm font-medium text-blue-900 dark:text-blue-300">{{ number_format($group['totalDuration']) }} ms</span>
                                                        </td>
                                                    @elseif($hasWastedBytes)
                                                        <td class="px-4 py-3 text-right">
                                                            <span class="text-sm font-medium text-blue-900 dark:text-blue-300">{{ number_format($group['totalWastedBytes'] / 1024, 1) }} KiB</span>
                                                        </td>
                                                    @endif
                                                </tr>

                                                <!-- Resources in Group -->
                                                @foreach($group['resources'] as $resource)
                                                    @php
                                                        $url = $resource['url'] ?? '';
                                                        $domain = parse_url($url, PHP_URL_HOST) ?? '';
                                                        $websiteDomain = parse_url($website->url, PHP_URL_HOST) ?? '';
                                                        $isThirdParty = $domain !== $websiteDomain && !str_ends_with($domain, '.' . $websiteDomain);
                                                        $resourceClass = ($isThirdParty ? 'third-party-resource-' . $index : '');
                                                    @endphp
                                                    <tr class="{{ $resourceClass }} hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                        <td class="px-4 py-3">
                                                            <div class="text-sm text-gray-900 dark:text-white">
                                                                @if(strlen($url) > 60)
                                                                    <span title="{{ $url }}">...{{ substr($url, -57) }}</span>
                                                                @else
                                                                    {{ $url }}
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                                            {{ number_format(($resource['totalBytes'] ?? 0) / 1024, 1) }} KiB
                                                        </td>
                                                        @if($isRenderBlocking)
                                                            <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                                                {{ number_format($resource['wastedMs'] ?? 0) }} ms
                                                            </td>
                                                        @elseif($hasWastedBytes)
                                                            <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                                                {{ number_format(($resource['wastedBytes'] ?? 0) / 1024, 1) }} KiB
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <!-- Additional Metrics -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Additional Metrics</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Speed Index</span>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($latestInsight->si, 2) }}s</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Time to First Byte</span>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($latestInsight->ttfb, 2) }}s</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Time to Interactive</span>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($latestInsight->interactive, 2) }}s</p>
            </div>
        </div>
    </div>

    <!-- Optimization Opportunities -->
    @php
        $opportunities = $latestInsight->opportunities ?? [];
    @endphp
    @if(!empty($opportunities))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Optimization Opportunities</h2>
        <div class="space-y-3">
            @foreach($opportunities as $key => $opportunity)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $opportunity['title'] }}</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($opportunity['description'], 200) }}</p>
                        @if(isset($opportunity['displayValue']))
                        <p class="mt-2 text-xs font-medium text-blue-600 dark:text-blue-400">{{ $opportunity['displayValue'] }}</p>
                        @endif
                    </div>
                    <span class="ml-4 text-xs font-medium text-red-600 dark:text-red-400">
                        {{ number_format((1 - $opportunity['score']) * 100, 1) }}% savings
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Debug Section - Raw API Response -->
    @if($latestInsight && $latestInsight->raw_data)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Raw API Response (Debug)</h2>
            <div class="flex items-center space-x-2">
                <button onclick="copyRawData()" id="copy-btn" class="px-4 py-2 text-sm bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span id="copy-text">Copy JSON</span>
                </button>
                <button onclick="toggleDebug()" class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <span id="debug-toggle-text">Show</span> Raw Data
                </button>
            </div>
        </div>
        <div id="debug-content" class="hidden">
            <div class="relative">
                <div class="bg-gray-900 dark:bg-black rounded-lg border border-gray-700" style="max-height: 600px; min-height: 300px; position: relative;">
                    <div class="absolute inset-0 overflow-y-auto overflow-x-auto p-4" style="max-height: 600px;">
                        <pre id="raw-json-content" class="text-xs text-green-400 font-mono whitespace-pre-wrap break-words break-all select-all" style="word-break: break-all; overflow-wrap: anywhere; max-width: 100%; margin: 0;">{{ json_encode(json_decode($latestInsight->raw_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
                <div class="absolute top-2 right-2 flex items-center space-x-2 z-10 pointer-events-none">
                    <div id="copy-success" class="hidden px-3 py-1 bg-green-500 text-white text-xs rounded-lg flex items-center space-x-1 shadow-lg pointer-events-auto">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Copied!</span>
                    </div>
                </div>
            </div>
            <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300 mb-2">Parsed Scores from Raw Data:</h3>
                @php
                    $rawData = json_decode($latestInsight->raw_data, true);
                    $categories = $rawData['lighthouseResult']['categories'] ?? [];
                @endphp
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Performance:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">
                            {{ isset($categories['performance']['score']) ? (int) round($categories['performance']['score'] * 100) : 'N/A' }}
                        </span>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Raw: {{ $categories['performance']['score'] ?? 'null' }}
                        </div>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Accessibility:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">
                            {{ isset($categories['accessibility']['score']) ? (int) round($categories['accessibility']['score'] * 100) : 'N/A' }}
                        </span>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Raw: {{ $categories['accessibility']['score'] ?? 'null' }}
                        </div>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Best Practices:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">
                            {{ isset($categories['best-practices']['score']) ? (int) round($categories['best-practices']['score'] * 100) : 'N/A' }}
                        </span>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Raw: {{ $categories['best-practices']['score'] ?? 'null' }}
                        </div>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">SEO:</span>
                        <span class="ml-2 text-gray-900 dark:text-white">
                            {{ isset($categories['seo']['score']) ? (int) round($categories['seo']['score'] * 100) : 'N/A' }}
                        </span>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Raw: {{ $categories['seo']['score'] ?? 'null' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Test History -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Test History</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Performance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Accessibility</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Best Practices</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">SEO</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($allInsights as $insight)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $insight->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <span class="font-medium">{{ $insight->performance_score ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <span class="font-medium">{{ $insight->accessibility_score ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <span class="font-medium">{{ $insight->best_practices_score ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <span class="font-medium">{{ $insight->seo_score ?? 'N/A' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No test history</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($allInsights->hasPages())
        <div class="mt-4">
            {{ $allInsights->links() }}
        </div>
        @endif
    </div>
    @endif
</div>

@push('scripts')
<script>
function toggleDebug() {
    const content = document.getElementById('debug-content');
    const toggleText = document.getElementById('debug-toggle-text');

    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        toggleText.textContent = 'Hide';
    } else {
        content.classList.add('hidden');
        toggleText.textContent = 'Show';
    }
}

function copyRawData() {
    const content = document.getElementById('raw-json-content');
    const text = content.textContent || content.innerText;
    const copyBtn = document.getElementById('copy-btn');
    const copyText = document.getElementById('copy-text');
    const copySuccess = document.getElementById('copy-success');

    // Create a temporary textarea to copy text
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();

    try {
        document.execCommand('copy');

        // Show success message
        copyText.textContent = 'Copied!';
        copySuccess.classList.remove('hidden');
        copyBtn.classList.add('bg-green-100', 'dark:bg-green-900/30');
        copyBtn.classList.remove('bg-blue-100', 'dark:bg-blue-900/30');

        // Reset after 2 seconds
        setTimeout(() => {
            copyText.textContent = 'Copy JSON';
            copySuccess.classList.add('hidden');
            copyBtn.classList.remove('bg-green-100', 'dark:bg-green-900/30');
            copyBtn.classList.add('bg-blue-100', 'dark:bg-blue-900/30');
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
        copyText.textContent = 'Failed';
        setTimeout(() => {
            copyText.textContent = 'Copy JSON';
        }, 2000);
    }

    document.body.removeChild(textarea);
}

function toggleInsight(insightId) {
    const content = document.getElementById(insightId + '-content');
    const arrow = document.getElementById(insightId + '-arrow');

    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        if (arrow) arrow.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        if (arrow) arrow.classList.remove('rotate-180');
    }
}

function toggleThirdPartyResources(index) {
    const checkbox = document.getElementById('show-third-party-' + index);
    const insightId = checkbox.getAttribute('data-insight-id');
    const rows = document.querySelectorAll('.' + 'third-party-resource-' + index);

    rows.forEach(row => {
        if (checkbox.checked) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Handle form submissions with loading states
document.addEventListener('DOMContentLoaded', function() {
    // Run first test form
    const runFirstForm = document.getElementById('run-first-test-form');
    if (runFirstForm) {
        runFirstForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('run-first-test-btn');
            const icon = document.getElementById('run-first-icon');
            const spinner = document.getElementById('run-first-spinner');
            const text = document.getElementById('run-first-text');

            btn.disabled = true;
            icon.classList.add('hidden');
            spinner.classList.remove('hidden');
            text.textContent = 'Running Test...';
        });
    }

    // Run new test form
    const runTestForm = document.getElementById('run-test-form');
    if (runTestForm) {
        runTestForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('run-test-btn');
            const icon = document.getElementById('run-test-icon');
            const spinner = document.getElementById('run-test-spinner');
            const text = document.getElementById('run-test-text');

            btn.disabled = true;
            icon.classList.add('hidden');
            spinner.classList.remove('hidden');
            text.textContent = 'Running Test...';
        });
    }

    // Auto-expand insights with "expanded-by-default" class (e.g., render-blocking)
    document.querySelectorAll('.expanded-by-default').forEach(content => {
        content.classList.remove('hidden');
        const contentId = content.id;
        const arrowId = contentId.replace('-content', '-arrow');
        const arrow = document.getElementById(arrowId);
        if (arrow) {
            arrow.classList.add('rotate-180');
        }
    });
});

// Screenshot Modal Functions
function openScreenshotModal(src, timing) {
    const modal = document.getElementById('screenshot-modal');
    const img = document.getElementById('modal-screenshot-img');
    const timeText = document.getElementById('modal-screenshot-time');

    img.src = src;
    if (timing) {
        timeText.textContent = 'Time: ' + timing;
    } else {
        timeText.textContent = '';
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeScreenshotModal() {
    const modal = document.getElementById('screenshot-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}
</script>
@endpush
@endsection

