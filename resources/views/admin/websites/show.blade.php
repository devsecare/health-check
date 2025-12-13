@extends('layouts.admin')

@section('title', 'Website Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Website Details</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View website information</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.websites.edit', $website) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                Edit
            </a>
            <a href="{{ route('admin.websites.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Back to Websites
            </a>
        </div>
    </div>
    
    <!-- Website Details -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="space-y-6">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold text-2xl">
                        {{ strtoupper(substr($website->name, 0, 1)) }}
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $website->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">ID: #{{ $website->id }}</p>
                </div>
            </div>
            
            <div class="pt-6 border-t border-gray-200 dark:border-gray-700 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Website URL</label>
                    <a href="{{ $website->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 flex items-center space-x-2">
                        <span class="text-lg font-medium">{{ $website->url }}</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Created</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $website->created_at->format('F d, Y') }} at {{ $website->created_at->format('h:i A') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Last Updated</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $website->updated_at->format('F d, Y') }} at {{ $website->updated_at->format('h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

