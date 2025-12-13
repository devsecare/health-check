@extends('layouts.admin')

@section('title', 'SEO Audit - ' . $website->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">SEO Audit</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $website->name }} - {{ $website->url }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.websites.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Back to Websites
            </a>
        </div>
    </div>

    @if(!$latestAudit)
    <!-- No Results -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="flex flex-col items-center justify-center text-center">
            <div class="relative mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center shadow-md">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            
            <div class="mb-6 max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No SEO audit results</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Run an SEO audit to analyze meta tags, headings, images, URL structure, internal linking, schema markup, Open Graph tags, robots.txt, and sitemap.
                </p>
            </div>
            
            <form id="run-seo-form" action="{{ route('admin.websites.seo-audit.run', $website) }}" method="POST">
                @csrf
                <button type="submit" id="run-seo-btn" class="group px-6 py-2.5 rounded-lg shadow-sm hover:shadow transition-all duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: #9333ea; border: none; cursor: pointer;">
                    <span class="flex items-center justify-center" style="color: #ffffff !important;">
                        <svg id="run-seo-icon" class="w-5 h-5 mr-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <svg id="run-seo-spinner" class="hidden w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="run-seo-text" class="text-sm font-medium" style="color: #ffffff !important;">Run SEO Audit</span>
                    </span>
                </button>
            </form>
        </div>
    </div>
    @else
    <!-- Run New Audit Button -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form id="run-new-seo-form" action="{{ route('admin.websites.seo-audit.run', $website) }}" method="POST" class="flex items-center justify-between">
            @csrf
            <div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Last audit: {{ $latestAudit->created_at->format('M d, Y H:i') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Run a new audit to get the latest SEO analysis</p>
            </div>
            <button type="submit" id="run-new-seo-btn" class="px-6 py-2 rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: #9333ea; color: #ffffff; border: none;">
                <span class="flex items-center space-x-2">
                    <svg id="run-new-seo-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg id="run-new-seo-spinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="run-new-seo-text">Run New Audit</span>
                </span>
            </button>
        </form>
    </div>

    <!-- Overall Score -->
    @php
        $score = $latestAudit->overall_score ?? 0;
        $scoreColor = ($score >= 80) ? 'text-green-600 dark:text-green-400' : (($score >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
        $barColorHex = ($score >= 80) ? '#16a34a' : (($score >= 50) ? '#ca8a04' : '#dc2626');
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Overall SEO Score</h2>
        <div class="flex items-center space-x-6">
            <div class="text-center">
                <span class="text-5xl font-bold {{ $scoreColor }}">{{ $score }}</span>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">out of 100</p>
            </div>
            <div class="flex-1">
                <div class="w-full rounded-full overflow-hidden" style="height: 16px; background-color: #e5e7eb; position: relative;">
                    @if($score > 0)
                    <div style="position: absolute; left: 0; top: 0; height: 16px; width: {{ min($score, 100) }}%; background-color: {{ $barColorHex }}; border-radius: 9999px; min-width: 2px; z-index: 1;"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Meta Tags -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Meta Tags</h2>
        @php $meta = $latestAudit->meta_tags ?? []; @endphp
        <div class="space-y-4">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Title</span>
                    @if($meta['title']['status'] ?? 'error' === 'good')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Good</span>
                    @elseif($meta['title']['status'] ?? 'error' === 'warning')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Warning</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Missing</span>
                    @endif
                </div>
                @if($meta['title']['exists'] ?? false)
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $meta['title']['content'] ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Length: {{ $meta['title']['length'] ?? 0 }} characters (optimal: 30-60)</p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No title tag found</p>
                @endif
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Description</span>
                    @if($meta['description']['status'] ?? 'error' === 'good')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Good</span>
                    @elseif($meta['description']['status'] ?? 'error' === 'warning')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Warning</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Missing</span>
                    @endif
                </div>
                @if($meta['description']['exists'] ?? false)
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $meta['description']['content'] ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Length: {{ $meta['description']['length'] ?? 0 }} characters (optimal: 120-160)</p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No meta description found</p>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Keywords</span>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ ($meta['keywords']['exists'] ?? false) ? 'Present' : 'Missing' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Viewport</span>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ ($meta['viewport']['exists'] ?? false) ? 'Present' : 'Missing' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Charset</span>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ ($meta['charset']['exists'] ?? false) ? ($meta['charset']['content'] ?? 'Present') : 'Missing' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Headings -->
    @php $headings = $latestAudit->headings ?? []; @endphp
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Heading Structure</h2>
        <div class="space-y-4">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">H1 Status</span>
                    @if(($headings['h1_status']['status'] ?? 'error') === 'good')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Good</span>
                    @elseif(($headings['h1_status']['status'] ?? 'error') === 'warning')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Warning</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Error</span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    H1 Count: {{ $headings['h1']['count'] ?? 0 }}
                    @if(($headings['h1_status']['has_multiple'] ?? false))
                        - Multiple H1 tags found
                    @elseif(($headings['h1_status']['has_none'] ?? false))
                        - No H1 tag found
                    @else
                        - Perfect (one H1)
                    @endif
                </p>
            </div>

            <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                @foreach(['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag)
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ $tag }}</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $headings[$tag]['count'] ?? 0 }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Images -->
    @php 
        $images = $latestAudit->images ?? [];
        $imagesList = $images['images'] ?? [];
        $imagesWithoutAlt = collect($imagesList)->filter(function($img) {
            return !($img['has_alt'] ?? false);
        })->values()->all();
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Images</h2>
        <div class="space-y-2 mb-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Images</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $images['total'] ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">With Alt Attributes</span>
                <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ $images['with_alt'] ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Without Alt Attributes</span>
                <span class="text-sm font-bold text-red-600 dark:text-red-400">{{ $images['without_alt'] ?? 0 }}</span>
            </div>
        </div>
        
        @if(!empty($imagesWithoutAlt) && count($imagesWithoutAlt) > 0)
        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Images Without Alt Attributes ({{ count($imagesWithoutAlt) }})</h3>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @foreach(array_slice($imagesWithoutAlt, 0, 50) as $image)
                <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">
                    <div class="flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ $image['src'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 dark:text-blue-400 hover:underline break-all font-medium">
                            {{ $image['src'] ?? 'N/A' }}
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                Missing Alt Attribute
                            </span>
                        </p>
                    </div>
                </div>
                @endforeach
                @if(count($imagesWithoutAlt) > 50)
                <div class="text-center pt-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Showing first 50 of {{ count($imagesWithoutAlt) }} images without alt attributes
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Additional Sections -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- URL Structure -->
        @php $urlStructure = $latestAudit->url_structure ?? []; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">URL Structure</h2>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                    @if(($urlStructure['status'] ?? 'error') === 'good')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Good</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Issues Found</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Length</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $urlStructure['length'] ?? 0 }} chars</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Depth</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $urlStructure['depth'] ?? 0 }} levels</span>
                </div>
                @if(!empty($urlStructure['issues'] ?? []))
                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Issues:</p>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            @foreach($urlStructure['issues'] as $issue)
                                <li class="flex items-start">
                                    <span class="mr-2">•</span>
                                    <span>{{ $issue }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <!-- Schema Markup -->
        @php $schema = $latestAudit->schema_markup ?? []; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Schema Markup</h2>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Has Schema</span>
                    @if($schema['has_schema'] ?? false)
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Yes</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">No</span>
                    @endif
                </div>
                @if($schema['has_schema'] ?? false)
                    <div class="mt-3 space-y-2">
                        @if($schema['json_ld']['exists'] ?? false)
                            <div class="text-xs text-gray-600 dark:text-gray-400">JSON-LD: {{ $schema['json_ld']['count'] ?? 0 }} found</div>
                        @endif
                        @if($schema['microdata']['exists'] ?? false)
                            <div class="text-xs text-gray-600 dark:text-gray-400">Microdata: {{ $schema['microdata']['count'] ?? 0 }} found</div>
                        @endif
                        @if($schema['rdfa']['exists'] ?? false)
                            <div class="text-xs text-gray-600 dark:text-gray-400">RDFa: {{ $schema['rdfa']['count'] ?? 0 }} found</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Open Graph -->
        @php $og = $latestAudit->open_graph ?? []; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Open Graph Tags</h2>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Found</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $og['found'] ?? 0 }} / {{ $og['total_required'] ?? 5 }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                    @if(($og['status'] ?? 'error') === 'good')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Good</span>
                    @elseif(($og['status'] ?? 'error') === 'warning')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Partial</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Missing</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Robots.txt & Sitemap -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Robots.txt & Sitemap</h2>
            <div class="space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Robots.txt</span>
                        @if($latestAudit->robots_txt['exists'] ?? false)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Found</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Missing</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-600 dark:text-gray-400">XML Sitemap</span>
                        @if($latestAudit->sitemap['exists'] ?? false)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Found</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Missing</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Internal Links -->
    @php $links = $latestAudit->internal_links ?? []; @endphp
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Internal Linking</h2>
        <div class="grid grid-cols-4 gap-4">
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Total Links</span>
                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $links['total'] ?? 0 }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Internal</span>
                <p class="text-sm font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $links['internal'] ?? 0 }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">External</span>
                <p class="text-sm font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $links['external'] ?? 0 }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">NoFollow</span>
                <p class="text-sm font-bold text-gray-600 dark:text-gray-400 mt-1">{{ $links['nofollow'] ?? 0 }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Audit History -->
    @if($allAudits->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Audit History</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">URL</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Score</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($allAudits as $audit)
                        @php
                            $score = $audit->overall_score ?? 0;
                            $scoreColor = ($score >= 80) ? 'text-green-600 dark:text-green-400' : (($score >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $audit->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($audit->url, 50) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-bold {{ $scoreColor }}">{{ $score }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $allAudits->links() }}
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle SEO audit forms
    const runSeoForm = document.getElementById('run-seo-form');
    const runNewSeoForm = document.getElementById('run-new-seo-form');
    
    if (runSeoForm) {
        runSeoForm.addEventListener('submit', function() {
            const btn = document.getElementById('run-seo-btn');
            const icon = document.getElementById('run-seo-icon');
            const spinner = document.getElementById('run-seo-spinner');
            const text = document.getElementById('run-seo-text');
            
            btn.disabled = true;
            icon.classList.add('hidden');
            spinner.classList.remove('hidden');
            text.textContent = 'Running Audit...';
        });
    }
    
    if (runNewSeoForm) {
        runNewSeoForm.addEventListener('submit', function() {
            const btn = document.getElementById('run-new-seo-btn');
            const icon = document.getElementById('run-new-seo-icon');
            const spinner = document.getElementById('run-new-seo-spinner');
            const text = document.getElementById('run-new-seo-text');
            
            btn.disabled = true;
            icon.classList.add('hidden');
            spinner.classList.remove('hidden');
            text.textContent = 'Running...';
        });
    }
});
</script>
@endpush
@endsection

