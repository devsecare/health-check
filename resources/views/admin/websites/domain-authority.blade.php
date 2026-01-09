@extends('layouts.admin')

@section('title', 'Domain Authority - ' . $website->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Domain Authority</h1>
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
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center shadow-md">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>

            <div class="mb-6 max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Domain Authority data</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Run a Domain Authority check to get Domain Authority, Page Authority, spam score, backlinks, and referring domains data.
                </p>
            </div>

            <form id="run-da-form" action="{{ route('admin.websites.domain-authority.run', $website) }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="send_email" value="1" id="send_email_da" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <label for="send_email_da" class="text-sm text-gray-700 dark:text-gray-300">Send email report when complete</label>
                </div>
                <button type="submit" id="run-da-btn" class="group px-6 py-2.5 rounded-lg shadow-sm hover:shadow transition-all duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: #2563eb; border: none; cursor: pointer;">
                    <span class="flex items-center justify-center" style="color: #ffffff !important;">
                        <svg id="run-da-icon" class="w-5 h-5 mr-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <svg id="run-da-spinner" class="hidden w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="run-da-text" class="text-sm font-medium" style="color: #ffffff !important;">Run Domain Authority Check</span>
                    </span>
                </button>
            </form>
        </div>
    </div>
    @else
    <!-- Run New Check Button -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form id="run-new-da-form" action="{{ route('admin.websites.domain-authority.run', $website) }}" method="POST" class="space-y-4">
            @csrf
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Last check: {{ $latestCheck->created_at->format('M d, Y H:i') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Run a new check to get updated Domain Authority data</p>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" name="send_email" value="1" id="send_email_new_da" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <label for="send_email_new_da" class="text-sm text-gray-700 dark:text-gray-300">Send email report</label>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" id="run-new-da-btn" class="px-6 py-2 rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: #2563eb; color: #ffffff; border: none;">
                <span class="flex items-center space-x-2">
                    <svg id="run-new-da-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg id="run-new-da-spinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="run-new-da-text">Run New Check</span>
                </span>
            </button>
            </div>
        </form>
    </div>

    <!-- Domain Authority Scores -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Domain Authority -->
        @php
            $da = $latestCheck->domain_authority ?? 0;
            $daColor = ($da >= 50) ? 'text-green-600 dark:text-green-400' : (($da >= 30) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
            $daBarColor = ($da >= 50) ? '#16a34a' : (($da >= 30) ? '#ca8a04' : '#dc2626');
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Domain Authority</h3>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold {{ $daColor }}">{{ $da }}</p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">out of 100</p>
            <div class="mt-4 w-full rounded-full overflow-hidden" style="height: 8px; background-color: #e5e7eb; position: relative;">
                @if($da > 0)
                <div style="position: absolute; left: 0; top: 0; height: 8px; width: {{ min($da, 100) }}%; background-color: {{ $daBarColor }}; border-radius: 9999px; min-width: 2px; z-index: 1;"></div>
                @endif
            </div>
        </div>

        <!-- Page Authority -->
        @php
            $pa = $latestCheck->page_authority ?? 0;
            $paColor = ($pa >= 50) ? 'text-green-600 dark:text-green-400' : (($pa >= 30) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
            $paBarColor = ($pa >= 50) ? '#16a34a' : (($pa >= 30) ? '#ca8a04' : '#dc2626');
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Page Authority</h3>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold {{ $paColor }}">{{ $pa }}</p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">out of 100</p>
            <div class="mt-4 w-full rounded-full overflow-hidden" style="height: 8px; background-color: #e5e7eb; position: relative;">
                @if($pa > 0)
                <div style="position: absolute; left: 0; top: 0; height: 8px; width: {{ min($pa, 100) }}%; background-color: {{ $paBarColor }}; border-radius: 9999px; min-width: 2px; z-index: 1;"></div>
                @endif
            </div>
        </div>

        <!-- Backlinks -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Backlinks</h3>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($latestCheck->backlinks ?? 0) }}</p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">total backlinks</p>
        </div>

        <!-- Referring Domains -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Referring Domains</h3>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($latestCheck->referring_domains ?? 0) }}</p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">unique domains</p>
        </div>
    </div>

    @if($latestCheck->spam_score !== null)
    <!-- Spam Score -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Spam Score</h3>
        @php
            $spamScore = $latestCheck->spam_score ?? 0;
            $spamColor = ($spamScore <= 1) ? 'text-green-600 dark:text-green-400' : (($spamScore <= 5) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
            $spamBarColor = ($spamScore <= 1) ? '#16a34a' : (($spamScore <= 5) ? '#ca8a04' : '#dc2626');
        @endphp
        <div class="flex items-center space-x-6">
            <div class="text-center">
                <span class="text-5xl font-bold {{ $spamColor }}">{{ $spamScore }}</span>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">out of 100</p>
            </div>
            <div class="flex-1">
                <div class="w-full rounded-full overflow-hidden" style="height: 16px; background-color: #e5e7eb; position: relative;">
                    @if($spamScore > 0)
                    <div style="position: absolute; left: 0; top: 0; height: 16px; width: {{ min($spamScore, 100) }}%; background-color: {{ $spamBarColor }}; border-radius: 9999px; min-width: 2px; z-index: 1;"></div>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    @if($spamScore <= 1)
                        Low spam risk
                    @elseif($spamScore <= 5)
                        Moderate spam risk
                    @else
                        High spam risk
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Domain Authority History Chart and Table -->
    @if(($historyData ?? collect())->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Domain Authority History of {{ parse_url($website->url, PHP_URL_HOST) ?: $website->url }}</h3>

        <!-- Line Chart -->
        @if(($monthlyData ?? collect())->count() > 0)
        <div class="mb-8" style="height: 400px;">
            <canvas id="daHistoryChart"></canvas>
        </div>
        @endif

        <!-- Historical Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Domain Authority</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        // Group by month and show the latest check for each month (to match the format in the image)
                        $groupedByMonth = $historyData->groupBy('date')->map(function($checks) {
                            // Get the latest check for each month
                            return $checks->sortByDesc('created_at')->first();
                        })->sortKeys();
                    @endphp
                    @forelse($groupedByMonth as $month => $check)
                        @php
                            $monthDA = $check['domain_authority'] ?? 0;
                            $monthDAColor = ($monthDA >= 50) ? 'text-green-600 dark:text-green-400' : (($monthDA >= 30) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                {{ $month }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-medium {{ $monthDAColor }}">{{ $monthDA }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">
                                No historical data available. Run Domain Authority checks to build history.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Detailed Check History Table (All Individual Checks) -->
    @if($allChecks->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Check History</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Domain Authority</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Page Authority</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Backlinks</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Referring Domains</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($allChecks as $check)
                        @php
                            $checkDA = $check->domain_authority ?? 0;
                            $checkDAColor = ($checkDA >= 50) ? 'text-green-600 dark:text-green-400' : (($checkDA >= 30) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                {{ $check->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-bold {{ $checkDAColor }}">{{ $checkDA }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                {{ $check->page_authority ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                {{ number_format($check->backlinks ?? 0) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                {{ number_format($check->referring_domains ?? 0) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Handle form submissions with loading states
    document.addEventListener('DOMContentLoaded', function() {
        const forms = ['run-da-form', 'run-new-da-form'];

        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', function(e) {
                    const btn = form.querySelector('button[type="submit"]');
                    const icon = form.querySelector('svg:not(.hidden)');
                    const spinner = form.querySelector('.animate-spin');
                    const text = form.querySelector('span:last-child');

                    if (btn && icon && spinner && text) {
                        btn.disabled = true;
                        icon.classList.add('hidden');
                        spinner.classList.remove('hidden');
                        text.textContent = 'Running...';
                    }
                });
            }
        });

        // Domain Authority History Chart
        @if(($monthlyData ?? collect())->count() > 0)
        const ctx = document.getElementById('daHistoryChart');
        if (ctx) {
            const chartLabels = @json($chartLabels ?? []);
            const chartData = @json($chartData ?? []);

            // Determine min and max for Y-axis with better range
            const minDA = Math.max(0, Math.min(...chartData) - 3);
            const maxDA = Math.min(100, Math.max(...chartData) + 3);

            // Ensure we have a reasonable range even with single data point
            const range = maxDA - minDA;
            const adjustedMin = range < 5 ? Math.max(0, minDA - 5) : minDA;
            const adjustedMax = range < 5 ? Math.min(100, maxDA + 5) : maxDA;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'DA',
                        data: chartData,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 12
                                },
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#e5e7eb' : '#374151'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#1f2937' : '#ffffff',
                            titleColor: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#e5e7eb' : '#374151',
                            bodyColor: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#e5e7eb' : '#374151',
                            borderColor: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#374151' : '#e5e7eb',
                            borderWidth: 1,
                            padding: 12
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: adjustedMin,
                            max: adjustedMax,
                            ticks: {
                                stepSize: 1,
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#9ca3af' : '#6b7280',
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#374151' : '#e5e7eb'
                            }
                        },
                        x: {
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#9ca3af' : '#6b7280',
                                font: {
                                    size: 11
                                },
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }
        @endif
    });
</script>
@endpush
@endsection
