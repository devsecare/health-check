@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your application settings</p>
    </div>
    
    <!-- Settings Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px" role="tablist">
                <button type="button" 
                        data-tab="general" 
                        class="tab-button active px-6 py-4 text-sm font-medium text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400">
                    General
                </button>
                <button type="button" 
                        data-tab="security" 
                        class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    Security
                </button>
                <button type="button" 
                        data-tab="notifications" 
                        class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    Notifications
                </button>
                <button type="button" 
                        data-tab="integrations" 
                        class="tab-button px-6 py-4 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    Integrations
                </button>
            </nav>
        </div>
        
        <!-- General Tab Content -->
        <div id="tab-general" class="tab-content p-6">
            <form class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Application Name
                    </label>
                    <input type="text" 
                           value="{{ config('app.name') }}" 
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Application URL
                    </label>
                    <input type="url" 
                           value="{{ url('/') }}" 
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Timezone
                    </label>
                    <select class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option>UTC</option>
                        <option>America/New_York</option>
                        <option>America/Los_Angeles</option>
                        <option>Europe/London</option>
                        <option>Asia/Dubai</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Language
                    </label>
                    <select class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option>English</option>
                        <option>Spanish</option>
                        <option>French</option>
                        <option>German</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Security Tab Content -->
        <div id="tab-security" class="tab-content p-6 hidden">
            <form class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Current Password
                    </label>
                    <input type="password" 
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        New Password
                    </label>
                    <input type="password" 
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Confirm New Password
                    </label>
                    <input type="password" 
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="flex items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <input type="checkbox" id="two-factor" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700">
                        <label for="two-factor" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Enable Two-Factor Authentication
                        </label>
                    </div>
                </div>
                
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Notifications Tab Content -->
        <div id="tab-notifications" class="tab-content p-6 hidden">
            <form class="space-y-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Notifications</h3>
                    
                    <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">New User Registrations</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Get notified when a new user registers</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">System Updates</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Receive notifications about system updates</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Weekly Reports</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Get weekly summary reports via email</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
                
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Integrations Tab Content -->
        <div id="tab-integrations" class="tab-content p-6 hidden">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Third-Party Integrations</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Slack Integration</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Connect with Slack for notifications</p>
                                </div>
                            </div>
                            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                                Connect
                            </button>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Google Analytics</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Track website analytics</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 rounded-full text-xs font-medium">
                                Connected
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Payment Gateway</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Stripe payment processing</p>
                                </div>
                            </div>
                            <button class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm font-medium transition-colors">
                                Configure
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection

