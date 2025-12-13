@extends('layouts.admin')

@section('title', 'Edit Website')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Website</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update website information</p>
        </div>
        <a href="{{ route('admin.websites.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <span class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Back to Websites</span>
            </span>
        </a>
    </div>
    
    <!-- Edit Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('admin.websites.update', $website) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Website Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Website Name <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $website->name) }}" 
                    required
                    placeholder="e.g., Google, Facebook, etc."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Website URL -->
            <div>
                <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Website URL <span class="text-red-500">*</span>
                </label>
                <input 
                    type="url" 
                    id="url" 
                    name="url" 
                    value="{{ old('url', $website->url) }}" 
                    required
                    placeholder="https://example.com"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('url')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Please include http:// or https:// in the URL</p>
            </div>
            
            <!-- Website Info Display -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Website ID:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">#{{ $website->id }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Created:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $website->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Last Updated:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $website->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.websites.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    Update Website
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

