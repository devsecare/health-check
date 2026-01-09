<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Test Email Route (for development/testing only)
Route::get('/test-email', function () {
    try {
        // Get or create a sample website
        $website = \App\Models\Website::first();
        if (!$website) {
            $website = \App\Models\Website::create([
                'name' => 'Test Website',
                'url' => 'https://example.com',
            ]);
        }

        // Get or create a sample PageSpeed insight
        $insight = $website->pageSpeedInsights()->latest()->first();
        if (!$insight) {
            $insight = \App\Models\PageSpeedInsight::create([
                'website_id' => $website->id,
                'strategy' => 'mobile',
                'performance_score' => 85,
                'accessibility_score' => 92,
                'seo_score' => 95,
                'best_practices_score' => 88,
                'lcp' => 2.5,
                'fcp' => 1.8,
                'cls' => 0.1,
                'tbt' => 200,
                'si' => 3.2,
                'ttfb' => 400,
                'interactive' => 3.5,
                'raw_data' => json_encode(['test' => 'data']),
            ]);
        }

        // Send test email
        \Illuminate\Support\Facades\Mail::to('developers@ecareinfoway.com')
            ->send(new \App\Mail\PageSpeedReportMail($insight, $website));

        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully to developers@ecareinfoway.com',
            'email_type' => 'PageSpeed Report',
            'website' => $website->name,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send test email: ' . $e->getMessage(),
            'error' => $e->getTraceAsString(),
        ], 500);
    }
})->name('test.email');

// Authentication Routes
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Admin Dashboard Routes (Protected)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [App\Http\Controllers\Admin\DashboardController::class, 'users'])->name('users');
    Route::get('/analytics', [App\Http\Controllers\Admin\DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/settings', [App\Http\Controllers\Admin\DashboardController::class, 'settings'])->name('settings');
    Route::get('/profile', [App\Http\Controllers\Admin\DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\Admin\DashboardController::class, 'updateProfile'])->name('profile.update');

    // User Management Routes
    Route::get('/users/create', [App\Http\Controllers\Admin\DashboardController::class, 'createUser'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\DashboardController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\DashboardController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\DashboardController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\DashboardController::class, 'deleteUser'])->name('users.delete');

    // Website Management Routes
    Route::resource('websites', App\Http\Controllers\Admin\WebsiteController::class);

    // PageSpeed Insights Routes
    Route::post('/websites/{website}/pagespeed', [App\Http\Controllers\Admin\WebsiteController::class, 'runPageSpeedTest'])->name('websites.pagespeed.run');
    Route::get('/websites/{website}/pagespeed', [App\Http\Controllers\Admin\WebsiteController::class, 'showPageSpeed'])->name('websites.pagespeed');

    // SEO Audit Routes
    Route::post('/websites/{website}/seo-audit', [App\Http\Controllers\Admin\WebsiteController::class, 'runSeoAudit'])->name('websites.seo-audit.run');
    Route::get('/websites/{website}/seo-audit', [App\Http\Controllers\Admin\WebsiteController::class, 'showSeoAudit'])->name('websites.seo-audit');

    // Broken Links Routes
    Route::post('/websites/{website}/broken-links', [App\Http\Controllers\Admin\WebsiteController::class, 'runBrokenLinksCheck'])->name('websites.broken-links.run');
    Route::get('/websites/{website}/broken-links/progress', [App\Http\Controllers\Admin\WebsiteController::class, 'getBrokenLinksProgress'])->name('websites.broken-links.progress');
    Route::get('/websites/{website}/broken-links', [App\Http\Controllers\Admin\WebsiteController::class, 'showBrokenLinks'])->name('websites.broken-links');

    // Domain Authority Routes
    Route::post('/websites/{website}/domain-authority', [App\Http\Controllers\Admin\WebsiteController::class, 'runDomainAuthorityCheck'])->name('websites.domain-authority.run');
    Route::get('/websites/{website}/domain-authority', [App\Http\Controllers\Admin\WebsiteController::class, 'showDomainAuthority'])->name('websites.domain-authority');

    // Notification Routes
    Route::get('/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/all', [App\Http\Controllers\Admin\NotificationController::class, 'showAll'])->name('notifications.show-all');
    Route::get('/notifications/unread-count', [App\Http\Controllers\Admin\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});
