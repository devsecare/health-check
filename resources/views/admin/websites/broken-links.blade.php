@extends('layouts.admin')

@section('title', 'Broken Links - ' . $website->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Broken Links Checker</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $website->name }} - {{ $website->url }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.websites.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Back to Websites
            </a>
        </div>
    </div>

    @if(!$latestCheck)
    <!-- No Results -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="flex flex-col items-center justify-center text-center">
            <div class="relative mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center shadow-md">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0L3 3m3.59 3.59L12 12m0 0l8.41 8.41M12 12l-8.41-8.41M12 12l8.41 8.41"/>
                    </svg>
                </div>
            </div>
            
            <div class="mb-6 max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No broken links check results</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Run a broken links check to crawl your website and find broken internal/external links, missing images, CSS, and JavaScript resources.
                </p>
            </div>
            
            <form id="run-broken-form" action="{{ route('admin.websites.broken-links.run', $website) }}" method="POST" class="space-y-4" onsubmit="return handleBrokenLinksSubmit(event);">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Check Type</label>
                        <select name="check_type" id="check_type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="whole_website">Whole Website</option>
                            <option value="single_page">Single Webpage</option>
                        </select>
                    </div>
                    <div id="single-page-url" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Page URL</label>
                        <input type="text" name="page_url" id="page_url" value="{{ $website->url }}" placeholder="https://example.com/page" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="send_email" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Send email report when complete</span>
                    </label>
                </div>
                <button type="submit" id="run-broken-btn" class="group px-6 py-2.5 rounded-lg shadow-sm hover:shadow transition-all duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: #ea580c; border: none; cursor: pointer;">
                    <span class="flex items-center justify-center" style="color: #ffffff !important;">
                        <svg id="run-broken-icon" class="w-5 h-5 mr-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0L3 3m3.59 3.59L12 12m0 0l8.41 8.41M12 12l-8.41-8.41M12 12l8.41 8.41"/>
                        </svg>
                        <svg id="run-broken-spinner" class="hidden w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="run-broken-text" class="text-sm font-medium" style="color: #ffffff !important;">Check Broken Links</span>
                    </span>
                </button>
            </form>
        </div>
    </div>
    @else
    <!-- Progress Bar for Active Check -->
    @php
        $activeCheck = $activeCheck ?? null;
    @endphp
    @if($activeCheck && in_array($activeCheck->status ?? '', ['pending', 'running']))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6" id="progress-container">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Checking Broken Links</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" id="progress-status">Initializing...</p>
            </div>
            <div class="text-right">
                <span id="progress-percentage" class="text-lg font-bold text-blue-600 dark:text-blue-400">0%</span>
            </div>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
            <div id="progress-bar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300 ease-out" style="width: 0%; background-image: linear-gradient(to right, #3b82f6, #2563eb);"></div>
        </div>
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span id="progress-details">Starting...</span>
            <span id="progress-time" class="text-xs"></span>
        </div>
    </div>
    @endif

    <!-- Run New Check Button -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form id="run-new-broken-form" action="{{ route('admin.websites.broken-links.run', $website) }}" method="POST" class="space-y-4" onsubmit="return handleBrokenLinksSubmit(event);">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <div>
                    @if($latestCheck)
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Last check: {{ $latestCheck->created_at->format('M d, Y H:i') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Run a new check to find the latest broken links</p>
                    @else
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">No previous checks</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Run a check to find broken links</p>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Check Type</label>
                    <select name="check_type" id="check_type_new" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        <option value="whole_website">Whole Website</option>
                        <option value="single_page">Single Webpage</option>
                    </select>
                </div>
                <div id="single-page-url-new" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Page URL</label>
                    <input type="text" name="page_url" id="page_url_new" value="{{ $website->url }}" placeholder="https://example.com/page" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                </div>
            </div>
            <div class="mb-4">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="send_email" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Send email report when complete</span>
                </label>
            </div>
            <div class="flex justify-end">
                <button type="submit" id="run-new-broken-btn" class="px-6 py-2 rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: #ea580c; color: #ffffff; border: none;">
                    <span class="flex items-center space-x-2">
                        <svg id="run-new-broken-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg id="run-new-broken-spinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="run-new-broken-text">Run New Check</span>
                    </span>
                </button>
            </div>
        </form>
    </div>

    @if($latestCheck && ($latestCheck->status ?? 'completed') === 'completed')
    <!-- Summary -->
    @php
        $summary = $latestCheck->summary ?? [];
        $totalChecked = $latestCheck->total_checked ?? 0;
        $totalBroken = $latestCheck->total_broken ?? 0;
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Check Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <span class="text-xs text-gray-500 dark:text-gray-400">Total Checked</span>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalChecked) }}</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <span class="text-xs text-gray-500 dark:text-gray-400">Total Broken</span>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($totalBroken) }}</p>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <span class="text-xs text-gray-500 dark:text-gray-400">Internal Broken</span>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($summary['internal'] ?? 0) }}</p>
            </div>
            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
                <span class="text-xs text-gray-500 dark:text-gray-400">External Broken</span>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ number_format($summary['external'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Broken Links by Type -->
    @php $byType = $summary['by_type'] ?? []; @endphp
    @if(!empty($byType))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Broken Links by Type</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($byType as $type => $count)
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ $type }}</span>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $count }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Broken Links by Status Code -->
    @php $byStatusCode = $summary['by_status_code'] ?? []; @endphp
    @if(!empty($byStatusCode))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Broken Links by Status Code</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach($byStatusCode as $code => $count)
                @php
                    $bgColor = ($code >= 500) ? 'bg-red-100 dark:bg-red-900/30' : (($code >= 400) ? 'bg-orange-100 dark:bg-orange-900/30' : 'bg-yellow-100 dark:bg-yellow-900/30');
                    $textColor = ($code >= 500) ? 'text-red-600 dark:text-red-400' : (($code >= 400) ? 'text-orange-600 dark:text-orange-400' : 'text-yellow-600 dark:text-yellow-400');
                @endphp
                <div class="{{ $bgColor }} rounded-lg p-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400">HTTP {{ $code ?: 'Error' }}</span>
                    <p class="text-xl font-bold {{ $textColor }} mt-1">{{ $count }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Broken Links List -->
    @php 
        $brokenLinks = $latestCheck->broken_links_data ?? [];
        // Ensure brokenLinks is an array
        if (is_string($brokenLinks)) {
            $brokenLinks = json_decode($brokenLinks, true) ?? [];
        }
        if (!is_array($brokenLinks)) {
            $brokenLinks = [];
        }
        
        // If brokenLinks is empty but totalBroken > 0, try to extract from raw_data
        if (empty($brokenLinks) && $totalBroken > 0 && !empty($latestCheck->raw_data)) {
            $rawData = json_decode($latestCheck->raw_data, true);
            if (isset($rawData['broken_links']) && is_array($rawData['broken_links'])) {
                $brokenLinks = $rawData['broken_links'];
                // Try to update the database with the extracted data
                try {
                    $latestCheck->broken_links_data = $brokenLinks;
                    $latestCheck->save();
                } catch (\Exception $e) {
                    \Log::warning('Failed to update broken_links_data from raw_data', [
                        'check_id' => $latestCheck->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    @endphp
    
    @if($totalBroken > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Broken Links Details</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Found {{ number_format($totalBroken) }} broken link{{ $totalBroken !== 1 ? 's' : '' }} out of {{ number_format($totalChecked) }} checked</p>
            </div>
        </div>
        
        @if(!empty($brokenLinks) && count($brokenLinks) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Broken URL</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Found On Page</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Error Message</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach(array_slice($brokenLinks, 0, 100) as $index => $link)
                        @php
                            $statusCode = $link['status_code'] ?? 0;
                            $statusColor = ($statusCode >= 500) ? 'text-red-600 dark:text-red-400' : (($statusCode >= 400) ? 'text-orange-600 dark:text-orange-400' : 'text-yellow-600 dark:text-yellow-400');
                            $statusBg = ($statusCode >= 500) ? 'bg-red-100 dark:bg-red-900/30' : (($statusCode >= 400) ? 'bg-orange-100 dark:bg-orange-900/30' : 'bg-yellow-100 dark:bg-yellow-900/30');
                            $typeColor = ($link['type'] === 'image') ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 
                                        (($link['type'] === 'css') ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' :
                                        (($link['type'] === 'javascript') ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'));
                            $isInternal = $link['is_internal'] ?? false;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 dark:text-blue-400 hover:underline break-all font-medium">
                                        {{ $link['url'] ?? 'N/A' }}
                                    </a>
                                    @if($isInternal)
                                        <span class="px-1.5 py-0.5 text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 rounded">Internal</span>
                                    @else
                                        <span class="px-1.5 py-0.5 text-xs bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 rounded">External</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColor }}">
                                    {{ ucfirst($link['type'] ?? 'link') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded {{ $statusBg }} {{ $statusColor }}">
                                    {{ $statusCode ?: 'Error' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if(!empty($link['found_on']))
                                    <a href="{{ $link['found_on'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:underline break-all">
                                        {{ Str::limit($link['found_on'], 50) }}
                                    </a>
                                @else
                                    <span class="text-xs text-gray-500 dark:text-gray-500 italic">Direct check</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-600 dark:text-gray-400" title="{{ $link['error'] ?? 'Unknown error' }}">
                                    {{ Str::limit($link['error'] ?? 'Unknown error', 60) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(count($brokenLinks) > 100)
            <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                Showing first 100 of {{ count($brokenLinks) }} broken links. 
                @if($latestCheck->raw_data)
                    <a href="#" onclick="document.getElementById('raw-data-section').classList.toggle('hidden'); return false;" class="text-blue-600 dark:text-blue-400 hover:underline ml-1">
                        View all in raw data
                    </a>
                @endif
            </div>
        @endif
        @else
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 p-4 text-center">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                <strong>{{ number_format($totalBroken) }} broken link{{ $totalBroken !== 1 ? 's' : '' }}</strong> were detected, but detailed information is not available.
            </p>
            @if($latestCheck->raw_data)
                <a href="#" onclick="document.getElementById('raw-data-section').classList.toggle('hidden'); return false;" class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-2 inline-block">
                    View raw data
                </a>
            @endif
        </div>
        @endif
    </div>
    @else
    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 p-6 text-center">
        <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-lg font-semibold text-green-900 dark:text-green-200 mb-1">No Broken Links Found!</h3>
        <p class="text-sm text-green-700 dark:text-green-300">All {{ $totalChecked }} checked links are working properly.</p>
    </div>
    @endif

    <!-- Raw Data Section (if broken links count > 0 but details not showing) -->
    @if($totalBroken > 0 && $latestCheck->raw_data)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hidden" id="raw-data-section">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Raw Check Data</h2>
            <button onclick="copyToClipboard('raw-broken-links-data')" class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                Copy JSON
            </button>
        </div>
        <div class="relative">
            <pre id="raw-broken-links-data" class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg text-xs text-gray-800 dark:text-gray-200 overflow-auto max-h-96 custom-scrollbar" style="word-break: break-all; overflow-wrap: anywhere;">{{ json_encode(json_decode($latestCheck->raw_data, true), JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    @endif

    <!-- Check History -->
    @if($allChecks->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Check History</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">URL</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Checked</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Broken</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($allChecks as $check)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $check->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($check->url, 50) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ number_format($check->total_checked ?? 0) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-bold {{ ($check->total_broken ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    {{ number_format($check->total_broken ?? 0) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $allChecks->links() }}
        </div>
    </div>
    @endif
    @endif
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle broken links forms
    const runBrokenForm = document.getElementById('run-broken-form');
    const runNewBrokenForm = document.getElementById('run-new-broken-form');
    
    // Handle form submission with AJAX to avoid timeout
    function handleBrokenLinksSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const btnText = form.querySelector('button[type="submit"] span:last-child');
        const btnIcon = form.querySelector('button[type="submit"] svg:not(.hidden)');
        const btnSpinner = form.querySelector('button[type="submit"] .animate-spin');
        
        // Disable button and show loading
        submitBtn.disabled = true;
        if (btnText) {
            btnText.textContent = 'Checking...';
        }
        if (btnIcon) {
            btnIcon.classList.add('hidden');
        }
        if (btnSpinner) {
            btnSpinner.classList.remove('hidden');
        }
        
        // Show progress container
        showProgressContainer();
        
        // Submit via AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.check_id) {
                // Start polling for progress
                activeCheckId = data.check_id;
                startProgressPolling();
            } else {
                // Error or immediate completion
                if (data.error) {
                    alert('Error: ' + data.error);
                    submitBtn.disabled = false;
                    if (btnText) btnText.textContent = 'Check Broken Links';
                } else {
                    // Reload page to show results
                    window.location.reload();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            submitBtn.disabled = false;
            if (btnText) btnText.textContent = 'Check Broken Links';
            if (btnIcon) btnIcon.classList.remove('hidden');
            if (btnSpinner) btnSpinner.classList.add('hidden');
        });
        
        return false;
    }
    
    function showProgressContainer() {
        let progressContainer = document.getElementById('progress-container');
        if (!progressContainer) {
            const runNewCheckDiv = document.querySelector('#run-new-broken-form')?.closest('.bg-white') || 
                                   document.querySelector('#run-broken-form')?.closest('.bg-white');
            if (runNewCheckDiv) {
                const progressHtml = `
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-4" id="progress-container">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Checking Broken Links</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" id="progress-status">Initializing...</p>
                            </div>
                            <div class="text-right">
                                <span id="progress-percentage" class="text-lg font-bold text-blue-600 dark:text-blue-400">0%</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
                            <div id="progress-bar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300 ease-out" style="width: 0%; background-image: linear-gradient(to right, #3b82f6, #2563eb);"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span id="progress-details">Starting...</span>
                            <span id="progress-time" class="text-xs"></span>
                        </div>
                    </div>
                `;
                runNewCheckDiv.insertAdjacentHTML('beforebegin', progressHtml);
                progressContainer = document.getElementById('progress-container');
            }
        }
        if (progressContainer) {
            progressContainer.style.display = 'block';
        }
    }

    // Toggle single page URL field
    const checkType = document.getElementById('check_type');
    const checkTypeNew = document.getElementById('check_type_new');
    const singlePageUrl = document.getElementById('single-page-url');
    const singlePageUrlNew = document.getElementById('single-page-url-new');
    
    if (checkType) {
        checkType.addEventListener('change', function() {
            singlePageUrl.style.display = this.value === 'single_page' ? 'block' : 'none';
        });
    }
    
    if (checkTypeNew) {
        checkTypeNew.addEventListener('change', function() {
            singlePageUrlNew.style.display = this.value === 'single_page' ? 'block' : 'none';
        });
    }

    // Note: Form submission is handled by handleBrokenLinksSubmit() via onsubmit attribute
    // No need for additional event listeners here

    // Progress polling
    let progressInterval = null;
    let activeCheckId = null;
    let isPolling = false;
    
    function startProgressPolling() {
        // Prevent multiple polling instances
        if (isPolling) {
            return;
        }
        
        // Check if there's an active check
        const progressContainer = document.getElementById('progress-container');
        if (!progressContainer) {
            // Try to create progress container if it doesn't exist, but don't start polling recursively
            showProgressContainer();
        }
        
        // Clear any existing interval
        if (progressInterval) {
            clearInterval(progressInterval);
        }
        
        // Mark as polling
        isPolling = true;
        
        // Start polling
        progressInterval = setInterval(checkProgress, 2000); // Poll every 2 seconds
        checkProgress(); // Initial check
    }

    function checkProgress() {
        const websiteId = {{ $website->id }};
        const url = `{{ route('admin.websites.broken-links.progress', $website) }}${activeCheckId ? '?check_id=' + activeCheckId : ''}`;
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'not_found') {
                stopProgressPolling();
                return;
            }
            
            if (data.check_id && !activeCheckId) {
                activeCheckId = data.check_id;
            }
            
            // If check is failed or cancelled, hide progress and stop polling immediately
            if (data.status === 'failed' || data.status === 'cancelled') {
                hideProgressContainer();
                stopProgressPolling();
                // Reload page immediately to show proper state
                setTimeout(() => {
                    window.location.reload();
                }, 500);
                return;
            }
            
            updateProgressBar(data.progress || 0, data.status, data.total_checked || 0, data.total_broken || 0);
            
            if (data.status === 'completed') {
                stopProgressPolling();
                // Reload page after 2 seconds to show results
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Progress check error:', error);
            // Continue polling even on error
        });
    }

    function updateProgressBar(progress, status, totalChecked, totalBroken) {
        const progressBar = document.getElementById('progress-bar');
        const progressPercentage = document.getElementById('progress-percentage');
        const progressStatus = document.getElementById('progress-status');
        const progressDetails = document.getElementById('progress-details');
        const progressContainer = document.getElementById('progress-container');
        
        if (!progressBar || !progressContainer) {
            return;
        }
        
        // Ensure progress container is visible
        if (progressContainer.classList.contains('hidden')) {
            progressContainer.classList.remove('hidden');
        }
        
        // Update progress bar width
        progressBar.style.width = progress + '%';
        progressPercentage.textContent = progress + '%';
        
        // Ensure gradient classes are always present and properly set
        // Remove all color classes first
        progressBar.classList.remove('from-blue-400', 'from-blue-500', 'to-blue-500', 'to-blue-600', 'from-green-500', 'to-green-600', 'from-red-500', 'to-red-600', 'from-gray-500', 'to-gray-600');
        
        // Ensure bg-gradient-to-r is always present
        if (!progressBar.classList.contains('bg-gradient-to-r')) {
            progressBar.classList.add('bg-gradient-to-r');
        }
        
        // Update status text and colors
        if (status === 'pending') {
            progressStatus.textContent = 'Queued...';
            progressDetails.textContent = 'Waiting to start';
            progressBar.classList.add('from-blue-400', 'to-blue-500');
            // Fallback gradient using inline style
            progressBar.style.backgroundImage = 'linear-gradient(to right, #60a5fa, #3b82f6)';
        } else if (status === 'running') {
            progressStatus.textContent = 'Checking links...';
            progressDetails.textContent = `Checked: ${totalChecked} pages, Found: ${totalBroken} broken links`;
            progressBar.classList.add('from-blue-500', 'to-blue-600');
            // Fallback gradient using inline style
            progressBar.style.backgroundImage = 'linear-gradient(to right, #3b82f6, #2563eb)';
        } else if (status === 'completed') {
            progressStatus.textContent = 'Completed!';
            progressDetails.textContent = `Finished: ${totalChecked} pages checked, ${totalBroken} broken links found`;
            progressBar.classList.add('from-green-500', 'to-green-600');
            // Fallback gradient using inline style
            progressBar.style.backgroundImage = 'linear-gradient(to right, #10b981, #059669)';
        } else if (status === 'failed') {
            progressStatus.textContent = 'Failed';
            progressDetails.textContent = 'The check encountered an error or was cancelled';
            progressBar.classList.add('from-red-500', 'to-red-600');
            // Fallback gradient using inline style
            progressBar.style.backgroundImage = 'linear-gradient(to right, #ef4444, #dc2626)';
            // Hide container after showing error message
            setTimeout(() => {
                hideProgressContainer();
            }, 2000);
        } else if (status === 'cancelled') {
            progressStatus.textContent = 'Cancelled';
            progressDetails.textContent = 'The check was cancelled';
            progressBar.classList.add('from-gray-500', 'to-gray-600');
            // Fallback gradient using inline style
            progressBar.style.backgroundImage = 'linear-gradient(to right, #6b7280, #4b5563)';
            // Hide container immediately
            setTimeout(() => {
                hideProgressContainer();
            }, 1000);
        }
    }

    function stopProgressPolling() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        isPolling = false;
    }

    function hideProgressContainer() {
        const progressContainer = document.getElementById('progress-container');
        if (progressContainer) {
            progressContainer.style.display = 'none';
            // Or fade out animation
            progressContainer.style.transition = 'opacity 0.3s ease-out';
            progressContainer.style.opacity = '0';
            setTimeout(() => {
                progressContainer.style.display = 'none';
            }, 300);
        }
    }

    function checkForActiveCheck() {
        // Check if there's an active check on page load
        @if(isset($activeCheck) && $activeCheck)
            activeCheckId = {{ $activeCheck->id }};
            // Only start polling if status is pending or running
            const checkStatus = '{{ $activeCheck->status ?? "" }}';
            
            if (checkStatus === 'pending' || checkStatus === 'running') {
                // Create progress container if it doesn't exist
                const existingContainer = document.getElementById('progress-container');
                if (!existingContainer) {
                    showProgressContainer();
                }
                
                // Start polling (only once)
                if (!isPolling) {
                    startProgressPolling();
                }
            } else {
                // Hide container if status is not active
                hideProgressContainer();
            }
        @endif
    }

    // Start polling if there's an active check on page load
    checkForActiveCheck();
    
    // Copy to clipboard function
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const text = element.textContent || element.innerText;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                // Show temporary success message
                const button = element.previousElementSibling?.querySelector('button') || 
                              element.closest('.relative')?.previousElementSibling?.querySelector('button');
                if (button) {
                    const originalText = button.textContent;
                    button.textContent = 'Copied!';
                    button.classList.add('bg-green-200', 'dark:bg-green-800');
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.classList.remove('bg-green-200', 'dark:bg-green-800');
                    }, 2000);
                }
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Copied to clipboard!');
            } catch (err) {
                console.error('Failed to copy:', err);
            }
            document.body.removeChild(textArea);
        }
    }
});
</script>
@endpush
@endsection

