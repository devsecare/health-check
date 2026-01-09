<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

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
