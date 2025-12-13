@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Welcome back! Here's what's happening today.</p>
    </div>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Stat Card 1: Total Websites -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Websites</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalWebsites ?? 0) }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Stat Card 2: PageSpeed Tests -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">PageSpeed Tests</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalPageSpeedTests ?? 0) }}</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Stat Card 3: Avg Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Performance</p>
                    @php
                        $avgPerf = round($avgPerformance ?? 0);
                        $perfColor = ($avgPerf >= 90) ? 'text-green-600 dark:text-green-400' : (($avgPerf >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                    @endphp
                    <p class="mt-2 text-3xl font-bold {{ $perfColor }}">{{ $avgPerf }}</p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">out of 100</p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Stat Card 4: SEO Audits -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">SEO Audits</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSeoAudits ?? 0) }}</p>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Chart Card 1: Activity Overview -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Activity Overview</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">Last 7 days</span>
            </div>
            <div class="h-64 flex items-end justify-between space-x-2">
                @php
                    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $maxCount = ($activityLast7Days ?? collect())->max(function($item) { return $item['total'] ?? 0; }) ?: 1;
                @endphp
                @foreach(($activityLast7Days ?? collect()) as $index => $dayData)
                    @php
                        $total = $dayData['total'] ?? 0;
                        $pageSpeed = $dayData['pageSpeed'] ?? 0;
                        $seo = $dayData['seo'] ?? 0;
                        $height = $maxCount > 0 ? ($total / $maxCount) * 100 : 0;
                        $dateObj = \Carbon\Carbon::parse($dayData['date'] ?? now());
                    @endphp
                    <div class="flex-1 flex flex-col items-center">
                        @php
                            // Ensure minimum height for visibility when there's data
                            $barHeight = $total > 0 ? max($height, 10) : 0;
                        @endphp
                        <div class="w-full relative mb-2" style="height: {{ $barHeight }}%; min-height: {{ $total > 0 ? '30px' : '0px' }};">
                            @if($total > 0)
                                @if($seo > 0)
                                <div class="absolute bottom-0 left-0 w-full bg-purple-500 rounded-t-lg" style="height: {{ $total > 0 ? ($seo / $total * 100) : 0 }}%"></div>
                                @endif
                                @if($pageSpeed > 0)
                                <div class="absolute bottom-0 left-0 w-full bg-blue-500 rounded-t-lg" style="height: {{ $total > 0 ? ($pageSpeed / $total * 100) : 0 }}%; {{ $seo > 0 ? 'bottom: ' . ($total > 0 ? ($seo / $total * 100) : 0) . '%;' : '' }}"></div>
                                @endif
                                @if($total > 0 && $pageSpeed == 0 && $seo == 0)
                                {{-- Fallback: show blue bar if we have total but no breakdown --}}
                                <div class="absolute bottom-0 left-0 w-full bg-blue-500 rounded-t-lg" style="height: 100%;"></div>
                                @endif
                            @endif
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $dateObj->format('D') }}</span>
                        <span class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $total }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex items-center justify-center space-x-4 text-xs">
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-blue-500 rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">PageSpeed Tests</span>
                </div>
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-purple-500 rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">SEO Audits</span>
                </div>
            </div>
        </div>
        
        <!-- Chart Card 2: Performance Trends -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performance Score</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">Average</span>
            </div>
            <div class="flex items-center justify-center h-64">
                @php
                    $avgPerf = round($avgPerformance ?? 0);
                    $perfColor = ($avgPerf >= 90) ? '#16a34a' : (($avgPerf >= 50) ? '#ca8a04' : '#dc2626');
                    $perfTextColor = ($avgPerf >= 90) ? 'text-green-600 dark:text-green-400' : (($avgPerf >= 50) ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                @endphp
                <div class="text-center">
                    <div class="relative w-32 h-32 mx-auto mb-4">
                        <svg class="transform -rotate-90 w-32 h-32">
                            <circle cx="64" cy="64" r="56" stroke="#e5e7eb" stroke-width="12" fill="none"/>
                            <circle cx="64" cy="64" r="56" stroke="{{ $perfColor }}" stroke-width="12" fill="none" 
                                stroke-dasharray="{{ 2 * M_PI * 56 }}" 
                                stroke-dashoffset="{{ 2 * M_PI * 56 * (1 - ($avgPerf / 100)) }}" 
                                stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-3xl font-bold {{ $perfTextColor }}">{{ $avgPerf }}</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Average Performance Score</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Based on {{ number_format($totalPageSpeedTests ?? 0) }} tests</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($recentActivities ?? [] as $activity)
            <a href="{{ $activity['url'] ?? '#' }}" class="block p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        @php
                            $iconColorClasses = [
                                'blue' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
                                'purple' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
                                'orange' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400',
                            ];
                            $iconColor = $iconColorClasses[$activity['iconColor'] ?? 'blue'] ?? $iconColorClasses['blue'];
                        @endphp
                        <div class="w-10 h-10 {{ $iconColor }} rounded-full flex items-center justify-center">
                            @if($activity['icon'] === 'bolt')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            @elseif($activity['icon'] === 'search')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0L3 3m3.59 3.59L12 12m0 0l8.41 8.41M12 12l-8.41-8.41M12 12l8.41 8.41"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity['title'] ?? 'Activity' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activity['description'] ?? '' }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $activity['time']->diffForHumans() ?? '' }}</p>
                    </div>
                    <div class="flex-shrink-0">
                        @if(isset($activity['score']))
                            @php
                                $score = $activity['score'];
                                $badgeColor = '';
                                if($activity['type'] === 'pagespeed' || $activity['type'] === 'seo') {
                                    $badgeColor = ($score >= 90) ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : (($score >= 50) ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400');
                                } else {
                                    $badgeColor = ($score === 0) ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                                }
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                @if($activity['type'] === 'broken-links')
                                    {{ $score }} broken
                                @else
                                    Score: {{ $score }}
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </a>
            @empty
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                <p>No recent activity</p>
            </div>
            @endforelse
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 text-center">
            <a href="{{ route('admin.analytics') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">View all analytics</a>
        </div>
    </div>
</div>
@endsection

