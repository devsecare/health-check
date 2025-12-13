@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($filter === 'unread')
                    Showing unread notifications
                @elseif($filter === 'read')
                    Showing read notifications
                @else
                    All notifications
                @endif
            </p>
        </div>
        <div class="flex items-center space-x-3">
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors text-sm">
                    Mark All as Read
                </button>
            </form>
            @endif
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex space-x-4">
            <a href="{{ route('admin.notifications.show-all', ['filter' => 'all']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $filter === 'all' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                All
            </a>
            <a href="{{ route('admin.notifications.show-all', ['filter' => 'unread']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors relative {{ $filter === 'unread' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Unread
                @if($unreadCount > 0 && $filter !== 'unread')
                    <span class="ml-2 px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">{{ $unreadCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.notifications.show-all', ['filter' => 'read']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $filter === 'read' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Read
            </a>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($notifications->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($notifications as $notification)
                    <div class="notification-item p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ !$notification->is_read ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}" 
                         data-notification-id="{{ $notification->id }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    @if(!$notification->is_read)
                                        <div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></div>
                                    @endif
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white {{ !$notification->is_read ? 'font-bold' : '' }}">
                                            {{ $notification->title }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $notification->message }}
                                        </p>
                                        <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                                            @if($notification->type)
                                                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                    {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($notification->url)
                                            <a href="{{ $notification->url }}" class="mt-3 inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                View Details
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="ml-4 flex items-center space-x-2">
                                @if(!$notification->is_read)
                                    <form method="POST" action="{{ route('admin.notifications.mark-read', $notification) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 text-xs text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 border border-gray-300 dark:border-gray-600 rounded hover:border-blue-600 dark:hover:border-blue-400 transition-colors">
                                            Mark as Read
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No notifications</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if($filter === 'unread')
                        You have no unread notifications.
                    @elseif($filter === 'read')
                        You have no read notifications.
                    @else
                        You don't have any notifications yet.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark as read on click (if not already read)
    document.querySelectorAll('.notification-item').forEach(item => {
        const notificationId = item.getAttribute('data-notification-id');
        const markAsReadForm = item.querySelector('form[action*="/read"]');
        
        if (markAsReadForm) {
            markAsReadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to reflect changes
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Failed to mark notification as read:', error);
                    // Fallback to form submission
                    this.submit();
                });
            });
        }
    });
});
</script>
@endpush
@endsection

