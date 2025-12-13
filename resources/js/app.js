import './bootstrap';

// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    function isSidebarVisible() {
        // Check if sidebar is actually visible by checking computed style
        const rect = sidebar.getBoundingClientRect();
        return rect.left >= 0;
    }

    function closeSidebar() {
        const mainContent = document.getElementById('main-content');
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('lg:translate-x-0'); // Remove lg override to ensure it's hidden
        
        if (window.innerWidth < 1024) {
            // On mobile, hide overlay
            if (sidebarOverlay) {
                sidebarOverlay.classList.add('hidden');
            }
        } else {
            // On desktop, adjust main content margin
            if (mainContent) {
                mainContent.classList.remove('lg:ml-64');
                mainContent.classList.add('lg:ml-0');
            }
        }
        document.body.style.overflow = '';
        // Store sidebar state
        localStorage.setItem('sidebar-closed', 'true');
    }

    function openSidebar() {
        const mainContent = document.getElementById('main-content');
        sidebar.classList.remove('-translate-x-full');
        
        if (window.innerWidth >= 1024) {
            // On desktop, restore lg:translate-x-0 and adjust main content
            sidebar.classList.add('lg:translate-x-0');
            if (mainContent) {
                mainContent.classList.remove('lg:ml-0');
                mainContent.classList.add('lg:ml-64');
            }
        } else {
            // On mobile, show overlay
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('hidden');
            }
            document.body.style.overflow = 'hidden';
        }
        // Store sidebar state
        localStorage.setItem('sidebar-closed', 'false');
    }

    // Restore sidebar state on load
    if (localStorage.getItem('sidebar-closed') === 'true' && window.innerWidth >= 1024) {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('lg:translate-x-0');
        const mainContent = document.getElementById('main-content');
        if (mainContent) {
            mainContent.classList.remove('lg:ml-64');
            mainContent.classList.add('lg:ml-0');
        }
    } else if (localStorage.getItem('sidebar-closed') === 'false' && window.innerWidth >= 1024) {
        // Ensure sidebar is visible on desktop if state says it should be open
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('lg:translate-x-0');
        const mainContent = document.getElementById('main-content');
        if (mainContent) {
            mainContent.classList.add('lg:ml-64');
            mainContent.classList.remove('lg:ml-0');
        }
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Use actual visibility check instead of class check
            const isVisible = isSidebarVisible();
            
            if (isVisible) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    // Handle window resize to maintain proper sidebar state
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const mainContent = document.getElementById('main-content');
            const isClosed = localStorage.getItem('sidebar-closed') === 'true';
            
            if (window.innerWidth >= 1024) {
                // Desktop: restore sidebar state from localStorage
                if (isClosed) {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('lg:translate-x-0');
                    if (mainContent) {
                        mainContent.classList.remove('lg:ml-64');
                        mainContent.classList.add('lg:ml-0');
                    }
                } else {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('lg:translate-x-0');
                    if (mainContent) {
                        mainContent.classList.add('lg:ml-64');
                        mainContent.classList.remove('lg:ml-0');
                    }
                }
                // Hide overlay on desktop
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('hidden');
                }
            } else {
                // Mobile: sidebar should be hidden by default
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('lg:translate-x-0');
                if (mainContent) {
                    mainContent.classList.remove('lg:ml-64');
                    mainContent.classList.remove('lg:ml-0');
                }
            }
        }, 100);
    });

    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isSidebarVisible()) {
            closeSidebar();
        }
    });

    // Notifications Dropdown
    const notificationsBtn = document.getElementById('notifications-btn');
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    const notificationsList = document.getElementById('notifications-list');
    const notificationsBadge = document.getElementById('notifications-badge');
    const notificationsCount = document.getElementById('notifications-count');
    const markAllReadBtn = document.getElementById('mark-all-read-btn');

    let notificationsLoaded = false;

    // Load notifications
    function loadNotifications(force = false) {
        // Don't reload if already loaded (unless forced)
        if (notificationsLoaded && !force) return;

        // Show loading state
        if (notificationsList) {
            notificationsList.innerHTML = '<div class="p-4 text-center text-gray-500 dark:text-gray-400"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-xs">Loading notifications...</p></div>';
        }

        fetch('/admin/notifications?limit=10', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            notificationsLoaded = true;
            if (data && data.notifications && Array.isArray(data.notifications)) {
                updateNotificationsList(data.notifications);
                updateNotificationsBadge(data.unread_count || 0);
            } else {
                throw new Error('Invalid response format');
            }
        })
        .catch(error => {
            console.error('Failed to load notifications:', error);
            notificationsLoaded = false; // Reset so it can retry
            if (notificationsList) {
                notificationsList.innerHTML = '<div class="p-4 text-center text-gray-500 dark:text-gray-400 text-xs">Failed to load notifications.<br><button onclick="window.loadNotifications(true)" class="mt-2 px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Retry</button></div>';
            }
        });
    }

    // Update notifications list
    function updateNotificationsList(notifications) {
        if (!notificationsList) return;

        if (notifications.length === 0) {
            notificationsList.innerHTML = '<div class="p-4 text-center text-gray-500 dark:text-gray-400 text-xs">No notifications</div>';
            if (markAllReadBtn) markAllReadBtn.classList.add('hidden');
            return;
        }

        let html = '';
        let hasUnread = false;

        notifications.forEach(notification => {
            if (!notification.is_read) hasUnread = true;

            const timeAgo = getTimeAgo(notification.created_at);
            const bgColor = notification.is_read ? '' : 'bg-blue-50 dark:bg-blue-900/20';
            const fontWeight = notification.is_read ? '' : 'font-semibold';

            html += `
                <div class="notification-item p-4 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer ${bgColor}" data-id="${notification.id}" data-read="${notification.is_read}" data-url="${notification.url || ''}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white ${fontWeight}">${escapeHtml(notification.title)}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">${escapeHtml(notification.message)}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">${timeAgo}</p>
                        </div>
                        ${!notification.is_read ? '<div class="ml-2 w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></div>' : ''}
                    </div>
                </div>
            `;
        });

        notificationsList.innerHTML = html;

        // Add click handlers
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-id');
                const isRead = this.getAttribute('data-read') === 'true';
                const url = this.getAttribute('data-url');

                if (!isRead) {
                    markNotificationAsRead(notificationId);
                }

                if (url) {
                    window.location.href = url;
                }
            });
        });

        // Show/hide mark all as read button
        if (markAllReadBtn) {
            if (hasUnread) {
                markAllReadBtn.classList.remove('hidden');
            } else {
                markAllReadBtn.classList.add('hidden');
            }
        }
    }

    // Update notifications badge
    function updateNotificationsBadge(count) {
        if (count > 0) {
            if (count > 9) {
                if (notificationsCount) {
                    notificationsCount.textContent = count > 99 ? '99+' : count;
                    notificationsCount.classList.remove('hidden');
                }
                if (notificationsBadge) notificationsBadge.classList.add('hidden');
            } else {
                if (notificationsBadge) {
                    notificationsBadge.classList.remove('hidden');
                }
                if (notificationsCount) {
                    notificationsCount.textContent = count;
                    notificationsCount.classList.remove('hidden');
                }
            }
        } else {
            if (notificationsBadge) notificationsBadge.classList.add('hidden');
            if (notificationsCount) notificationsCount.classList.add('hidden');
        }
    }

    // Mark notification as read
    function markNotificationAsRead(notificationId) {
        fetch(`/admin/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload notifications
                notificationsLoaded = false;
                loadNotifications();
                updateUnreadCount();
            }
        })
        .catch(error => console.error('Failed to mark notification as read:', error));
    }

    // Mark all as read
    function markAllAsRead() {
        fetch('/admin/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificationsLoaded = false;
                loadNotifications();
                updateUnreadCount();
            }
        })
        .catch(error => console.error('Failed to mark all as read:', error));
    }

    // Update unread count
    function updateUnreadCount() {
        fetch('/admin/notifications/unread-count', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            updateNotificationsBadge(data.count);
        })
        .catch(error => console.error('Failed to get unread count:', error));
    }

    // Helper functions
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
        if (diffInSeconds < 604800) return Math.floor(diffInSeconds / 86400) + ' days ago';

        return date.toLocaleDateString();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    if (notificationsBtn && notificationsDropdown) {
        notificationsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const wasHidden = notificationsDropdown.classList.contains('hidden');
            notificationsDropdown.classList.toggle('hidden');

            // Load notifications when dropdown opens (was hidden, now open)
            if (wasHidden) {
                loadNotifications(true); // Force reload
            }
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!notificationsBtn.contains(e.target) && !notificationsDropdown.contains(e.target)) {
                notificationsDropdown.classList.add('hidden');
            }
        });

        // Mark all as read button
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAllAsRead();
            });
        }
    }

    // Make loadNotifications globally accessible for retry button
    window.loadNotifications = loadNotifications;

    // Load unread count on page load
    if (notificationsBtn) {
        updateUnreadCount();
        // Refresh unread count every 30 seconds
        setInterval(updateUnreadCount, 30000);
    }

    // User Menu Dropdown
    const userMenuBtn = document.getElementById('user-menu-btn');
    const userMenuDropdown = document.getElementById('user-menu-dropdown');

    if (userMenuBtn && userMenuDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('hidden');
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                userMenuDropdown.classList.add('hidden');
            }
        });
    }

    // Settings Tabs
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabButtons.length > 0 && tabContents.length > 0) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');

                // Remove active state from all buttons
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'text-blue-600', 'dark:text-blue-400', 'border-b-2', 'border-blue-600', 'dark:border-blue-400');
                    btn.classList.add('text-gray-500', 'dark:text-gray-400');
                });

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Add active state to clicked button
                this.classList.add('active', 'text-blue-600', 'dark:text-blue-400', 'border-b-2', 'border-blue-600', 'dark:border-blue-400');
                this.classList.remove('text-gray-500', 'dark:text-gray-400');

                // Show target tab content
                const targetContent = document.getElementById('tab-' + targetTab);
                if (targetContent) {
                    targetContent.classList.remove('hidden');
                }
            });
        });
    }
});
