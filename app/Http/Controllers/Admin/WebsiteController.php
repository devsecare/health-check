<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\PageSpeedInsight;
use App\Models\SeoAudit;
use App\Models\BrokenLink;
use App\Models\DomainAuthority;
use App\Services\PageSpeedInsightsService;
use App\Services\SeoAuditService;
use App\Services\BrokenLinksService;
use App\Services\DomainAuthorityService;
use App\Services\NotificationService;
use App\Traits\HasWebsiteAccess;
use App\Jobs\CheckBrokenLinks;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    use HasWebsiteAccess;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Start with accessible websites
        $query = Website::accessibleBy($user);

        // For super admin, add filtering by user
        $selectedUserId = null;
        $allUsers = collect();

        if ($user->isSuperAdmin()) {
            // Get all users for filter dropdown
            $allUsers = \App\Models\User::orderBy('name')->get();

            // Apply user filter if provided
            if ($request->has('user_id') && $request->user_id) {
                $selectedUserId = $request->user_id;
                $query->whereHas('users', function($q) use ($selectedUserId) {
                    $q->where('users.id', $selectedUserId);
                });
            }

            // Eager load users relationship for tags
            $query->with('users');
        }

        $websites = $query->latest()->paginate(10)->withQueryString();

        // Get remaining website slots for regular users
        $remainingSlots = null;
        if (!$user->isSuperAdmin()) {
            $remainingSlots = $user->getRemainingWebsiteSlots();
        }

        return view('admin.websites.index', compact('websites', 'remainingSlots', 'allUsers', 'selectedUserId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        // Check if user can create websites
        if (!$user->canCreateWebsite()) {
            $remaining = $user->getRemainingWebsiteSlots();
            $message = $remaining === null
                ? 'You cannot create more websites at this time.'
                : "You have reached your website limit ({$user->website_limit} websites).";
            return redirect()->route('admin.websites.index')
                ->with('error', $message);
        }

        $remainingSlots = $user->getRemainingWebsiteSlots();

        return view('admin.websites.create', compact('remainingSlots'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Check if user can create websites
        if (!$user->canCreateWebsite()) {
            $remaining = $user->getRemainingWebsiteSlots();
            $message = $remaining === null
                ? 'You cannot create more websites at this time.'
                : "You have reached your website limit ({$user->website_limit} websites).";
            return redirect()->route('admin.websites.index')
                ->with('error', $message);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
        ]);

        $website = Website::create($validated);

        // Auto-assign website to the creator if they're a regular user
        if (!$user->isSuperAdmin()) {
            $user->websites()->attach($website->id);
        }

        return redirect()->route('admin.websites.index')->with('success', 'Website added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Website $website)
    {
        $this->ensureWebsiteAccess($website->id);
        return view('admin.websites.show', compact('website'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Website $website)
    {
        $user = auth()->user();
        $this->ensureWebsiteAccess($website->id);

        // Only super admin or the website creator can edit websites
        // For regular users, they can only edit websites they created (have access to)
        // Super admin can edit any website

        return view('admin.websites.edit', compact('website'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Website $website)
    {
        $user = auth()->user();
        $this->ensureWebsiteAccess($website->id);

        // Regular users can update websites they have access to
        // Super admin can update any website

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
        ]);

        $website->update($validated);

        return redirect()->route('admin.websites.index')->with('success', 'Website updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Website $website)
    {
        $user = auth()->user();
        $this->ensureWebsiteAccess($website->id);

        // Regular users can delete websites they have access to
        // Super admin can delete any website

        $website->delete();

        return redirect()->route('admin.websites.index')->with('success', 'Website deleted successfully!');
    }

    /**
     * Run PageSpeed Insights test for a website
     */
    public function runPageSpeedTest(Website $website, Request $request)
    {
        $this->ensureWebsiteAccess($website->id);

        try {
            // Increase execution time limit for PageSpeed API calls (can take 30-120 seconds)
            set_time_limit(180); // 3 minutes
            ini_set('max_execution_time', 180);

            $strategy = $request->input('strategy', 'mobile');
            $sendEmail = $request->has('send_email') && $request->input('send_email') == '1';

            $service = new PageSpeedInsightsService();
            $result = $service->runTest($website->url, $strategy);

            if (!$result) {
                return back()->with('error', 'Failed to run PageSpeed Insights test. The API may be temporarily unavailable or the URL may be unreachable. Please try again.');
            }

            $insight = PageSpeedInsight::create([
                'website_id' => $website->id,
                'strategy' => $strategy,
                'performance_score' => $result['performance_score'],
                'accessibility_score' => $result['accessibility_score'],
                'seo_score' => $result['seo_score'],
                'best_practices_score' => $result['best_practices_score'],
                'lcp' => $result['lcp'],
                'fcp' => $result['fcp'],
                'cls' => $result['cls'],
                'tbt' => $result['tbt'],
                'si' => $result['si'],
                'ttfb' => $result['ttfb'],
                'interactive' => $result['interactive'],
                'raw_data' => $result['raw_data'],
            ]);

            // Create notification for PageSpeed test completion
            try {
                NotificationService::notifyPageSpeedTestCompleted($website, $result['performance_score'], $strategy);
            } catch (\Exception $e) {
                \Log::warning('Failed to create notification for PageSpeed test', [
                    'website_id' => $website->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Send email report if requested
            if ($sendEmail) {
                try {
                    $user = auth()->user();
                    if ($user && $user->email) {
                        \Illuminate\Support\Facades\Mail::to($user->email)
                            ->send(new \App\Mail\PageSpeedReportMail($insight->fresh(), $website->fresh()));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send PageSpeed report email', [
                        'website_id' => $website->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return redirect()->route('admin.websites.pagespeed', $website)
                ->with('success', 'PageSpeed Insights test completed successfully!');

        } catch (\Exception $e) {
            \Log::error('PageSpeed Test Error', [
                'website_id' => $website->id,
                'url' => $website->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while running the PageSpeed test: ' . $e->getMessage());
        }
    }

    /**
     * Show PageSpeed Insights results for a website
     */
    public function showPageSpeed(Website $website, Request $request)
    {
        $this->ensureWebsiteAccess($website->id);

        $strategy = $request->input('strategy', 'mobile');

        $latestInsight = $website->latestPageSpeedInsight($strategy);
        $allInsights = $website->pageSpeedInsights()
            ->where('strategy', $strategy)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.websites.pagespeed', compact('website', 'latestInsight', 'allInsights', 'strategy'));
    }

    /**
     * Run SEO audit for a website
     */
    public function runSeoAudit(Website $website, Request $request)
    {
        $this->ensureWebsiteAccess($website->id);

        try {
            set_time_limit(180);
            ini_set('max_execution_time', 180);

            $url = $request->input('url', $website->url);
            $sendEmail = $request->has('send_email') && $request->input('send_email') == '1';

            $service = new SeoAuditService();
            $result = $service->runAudit($url);

            if (!$result) {
                return back()->with('error', 'Failed to run SEO audit. The URL may be unreachable or the page may not be accessible.');
            }

            // Calculate overall score
            $audit = new SeoAudit();
            $audit->meta_tags = $result['meta_tags'] ?? [];
            $audit->headings = $result['headings'] ?? [];
            $audit->images = $result['images'] ?? [];
            $audit->url_structure = $result['url_structure'] ?? [];
            $audit->internal_links = $result['internal_links'] ?? [];
            $audit->schema_markup = $result['schema_markup'] ?? [];
            $audit->open_graph = $result['open_graph'] ?? [];
            $audit->robots_txt = $result['robots_txt'] ?? [];
            $audit->sitemap = $result['sitemap'] ?? [];

            $overallScore = $audit->calculateOverallScore();

            $auditRecord = SeoAudit::create([
                'website_id' => $website->id,
                'url' => $url,
                'meta_tags' => $result['meta_tags'] ?? [],
                'headings' => $result['headings'] ?? [],
                'images' => $result['images'] ?? [],
                'url_structure' => $result['url_structure'] ?? [],
                'internal_links' => $result['internal_links'] ?? [],
                'schema_markup' => $result['schema_markup'] ?? [],
                'open_graph' => $result['open_graph'] ?? [],
                'robots_txt' => $result['robots_txt'] ?? [],
                'sitemap' => $result['sitemap'] ?? [],
                'overall_score' => $overallScore,
                'raw_data' => json_encode($result),
            ]);

            // Create notification for SEO audit completion
            try {
                NotificationService::notifySeoAuditCompleted($website, $overallScore);
            } catch (\Exception $e) {
                \Log::warning('Failed to create notification for SEO audit', [
                    'website_id' => $website->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Send email report if requested
            if ($sendEmail) {
                try {
                    $user = auth()->user();
                    if ($user && $user->email) {
                        \Illuminate\Support\Facades\Mail::to($user->email)
                            ->send(new \App\Mail\SeoAuditReportMail($auditRecord->fresh(), $website->fresh()));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send SEO audit report email', [
                        'website_id' => $website->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return redirect()->route('admin.websites.seo-audit', $website)
                ->with('success', 'SEO audit completed successfully!');

        } catch (\Exception $e) {
            \Log::error('SEO Audit Error', [
                'website_id' => $website->id,
                'url' => $url ?? $website->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while running the SEO audit: ' . $e->getMessage());
        }
    }

    /**
     * Show SEO audit results for a website
     */
    public function showSeoAudit(Website $website)
    {
        $this->ensureWebsiteAccess($website->id);

        $latestAudit = $website->latestSeoAudit();
        $allAudits = $website->seoAudits()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.websites.seo-audit', compact('website', 'latestAudit', 'allAudits'));
    }

    /**
     * Run broken links check for a website (synchronous)
     */
    public function runBrokenLinksCheck(Website $website, Request $request)
    {
        $this->ensureWebsiteAccess($website->id);

        try {
            $checkType = $request->input('check_type', 'whole_website');
            $url = $checkType === 'single_page'
                ? ($request->input('page_url', $website->url))
                : ($request->input('url', $website->url));

            // For single page, set depth to 0 and pages to 1
            $maxDepth = $checkType === 'single_page' ? 0 : $request->input('max_depth', 2);
            $maxPages = $checkType === 'single_page' ? 1 : $request->input('max_pages', 30);

            $sendEmail = $request->has('send_email') && $request->input('send_email') == '1';

            // Create a pending check record
            $checkRecord = BrokenLink::create([
                'website_id' => $website->id,
                'url' => $url,
                'progress' => 0,
                'status' => 'pending',
                'job_id' => null,
            ]);

            // For all requests, use background execution to avoid timeout
            // Return response immediately, then run check after response is sent

            // Check if AJAX request
            $isAjax = $request->wantsJson() || $request->ajax();

            // If FastCGI is available, finish request and continue processing
            if (function_exists('fastcgi_finish_request')) {
                // Send response to client immediately
                if ($isAjax) {
                    $response = response()->json([
                        'success' => true,
                        'message' => 'Broken links check started',
                        'check_id' => $checkRecord->id,
                    ]);
                    $response->send();
                } else {
                    // For non-AJAX, redirect with message
                    $redirect = redirect()->route('admin.websites.broken-links', $website)
                        ->with('success', 'Broken links check started. Progress will update automatically.');
                    $redirect->send();
                }

                // Continue processing in background (won't timeout)
                ignore_user_abort(true);
                set_time_limit(600); // 10 minutes for background process
                ini_set('max_execution_time', 600);

                $this->executeBrokenLinksCheck($checkRecord, $website, $url, $maxDepth, $maxPages, $sendEmail);

                // Response already sent
                exit;
            }

            // Fallback: Use register_shutdown_function for non-FastCGI
            register_shutdown_function(function() use ($checkRecord, $website, $url, $maxDepth, $maxPages, $sendEmail) {
                ignore_user_abort(true);
                set_time_limit(300);
                $this->executeBrokenLinksCheck($checkRecord, $website, $url, $maxDepth, $maxPages, $sendEmail);
            });

            // Return appropriate response
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Broken links check started',
                    'check_id' => $checkRecord->id,
                ]);
            }

            return redirect()->route('admin.websites.broken-links', $website)
                ->with('success', 'Broken links check started. Progress will update automatically.');


        } catch (\Exception $e) {
            \Log::error('Broken Links Check Error', [
                'website_id' => $website->id,
                'url' => $url ?? $website->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark check as failed if it exists
            if (isset($checkRecord)) {
                $checkRecord->update([
                    'status' => 'failed',
                    'progress' => 0
                ]);
            }

            return back()->with('error', 'An error occurred during the broken links check: ' . $e->getMessage());
        }
    }

    /**
     * Execute broken links check (called in background)
     */
    private function executeBrokenLinksCheck($checkRecord, $website, $url, $maxDepth, $maxPages, $sendEmail): void
    {
        try {
            \Log::info('Starting background broken links check', [
                'check_id' => $checkRecord->id,
                'website_id' => $website->id,
                'url' => $url
            ]);

            // Update status to running with timestamp
            $checkRecord->update([
                'status' => 'running',
                'progress' => 5,
                'updated_at' => now() // Ensure updated_at is set
            ]);

            // Track broken links in progress callback so we can save them even if final save fails
            $capturedBrokenLinks = [];
            $capturedTotalChecked = 0;
            $capturedTotalBroken = 0;

            // Create service with progress callback
            $service = new \App\Services\BrokenLinksService();
            $service->setProgressCallback(function($progress, $message = '', $totalChecked = null, $totalBroken = null) use ($checkRecord, $service, &$capturedBrokenLinks, &$capturedTotalChecked, &$capturedTotalBroken) {
                $updateData = [
                    'progress' => min(100, max(0, $progress)),
                    'status' => 'running' // Ensure status stays as running during progress updates
                ];

                // Update total_checked and total_broken if provided
                if ($totalChecked !== null) {
                    $updateData['total_checked'] = $totalChecked;
                    $capturedTotalChecked = $totalChecked;
                }
                if ($totalBroken !== null) {
                    $updateData['total_broken'] = $totalBroken;
                    $capturedTotalBroken = $totalBroken;

                    // Always try to capture broken links data whenever we have a count update
                    // This ensures we save data even if the final save fails or the process hangs
                    if ($totalBroken > 0) {
                        try {
                            $currentBrokenLinks = $service->getBrokenLinks();
                            if (!empty($currentBrokenLinks) && count($currentBrokenLinks) > 0) {
                                $capturedBrokenLinks = $currentBrokenLinks;
                                // Save broken links data incrementally as they're found
                                // Only update if we have more links than currently saved
                                $currentSavedCount = is_array($checkRecord->broken_links_data) ? count($checkRecord->broken_links_data) : 0;
                                if (count($currentBrokenLinks) > $currentSavedCount) {
                                    $updateData['broken_links_data'] = $currentBrokenLinks;
                                }
                            }
                        } catch (\Exception $e) {
                            // Silent fail - don't spam logs
                        }
                    }
                }

                $checkRecord->update($updateData);
            });

            // Run the check with timeout protection
            $result = null;
            try {
                \Log::info('Calling service runCheck', [
                    'check_id' => $checkRecord->id,
                    'url' => $url,
                    'maxDepth' => $maxDepth,
                    'maxPages' => $maxPages
                ]);

                $result = $service->runCheck($url, $maxDepth, $maxPages);

                \Log::info('Service runCheck completed', [
                    'check_id' => $checkRecord->id,
                    'result_exists' => $result !== null,
                    'broken_links_count' => $result ? count($result['broken_links'] ?? []) : 0,
                    'total_checked' => $result['total_checked'] ?? 0,
                    'total_broken' => $result['total_broken'] ?? 0
                ]);
            } catch (\Exception $serviceException) {
                \Log::error('Service exception during broken links check', [
                    'check_id' => $checkRecord->id,
                    'website_id' => $website->id,
                    'url' => $url,
                    'error' => $serviceException->getMessage(),
                    'trace' => $serviceException->getTraceAsString()
                ]);
            }

            if (!$result) {
                $checkRecord->update([
                    'status' => 'failed',
                    'progress' => 0,
                    'total_checked' => 0,
                    'total_broken' => 0
                ]);

                \Log::error('Broken Links Check: No result returned', [
                    'website_id' => $website->id,
                    'url' => $url
                ]);
                return;
            }

            // Ensure we have valid counts from result (should be set by final progress update)
            $totalChecked = $result['total_checked'] ?? $checkRecord->fresh()->total_checked ?? 0;
            $totalBroken = $result['total_broken'] ?? $checkRecord->fresh()->total_broken ?? 0;

            // Refresh the record to get latest values from progress callback
            $checkRecord->refresh();

            // Use values from database if they're more recent (from progress callback)
            if ($checkRecord->total_checked > $totalChecked) {
                $totalChecked = $checkRecord->total_checked;
            }
            if ($checkRecord->total_broken > $totalBroken) {
                $totalBroken = $checkRecord->total_broken;
            }

            // Ensure broken_links array is properly formatted
            $brokenLinksData = $result['broken_links'] ?? [];
            if (!is_array($brokenLinksData)) {
                $brokenLinksData = [];
            }

            // If result is empty but we captured data from progress callback, use that
            if (empty($brokenLinksData) && !empty($capturedBrokenLinks)) {
                \Log::info('Using captured broken links from progress callback', [
                    'check_id' => $checkRecord->id,
                    'captured_count' => count($capturedBrokenLinks)
                ]);
                $brokenLinksData = $capturedBrokenLinks;
            }

            // If we have broken links count but no data, something went wrong
            if (empty($brokenLinksData) && $totalBroken > 0) {
                \Log::warning('Broken links data is empty but total_broken > 0', [
                    'check_id' => $checkRecord->id,
                    'total_broken' => $totalBroken,
                    'result_keys' => $result ? array_keys($result) : [],
                    'result_broken_links_type' => isset($result['broken_links']) ? gettype($result['broken_links']) : 'not set',
                    'captured_broken_links_count' => count($capturedBrokenLinks),
                    'captured_total_checked' => $capturedTotalChecked,
                    'captured_total_broken' => $capturedTotalBroken
                ]);
            }

            // Log for debugging
            \Log::info('Saving broken links data', [
                'check_id' => $checkRecord->id,
                'broken_links_count' => count($brokenLinksData),
                'total_broken' => $totalBroken,
                'is_array' => is_array($brokenLinksData),
                'result_total_broken' => $result['total_broken'] ?? 'not set'
            ]);

            // Update with final results - FORCE status to completed and progress to 100
            $updateResult = $checkRecord->update([
                'status' => 'completed',
                'progress' => 100,
                'summary' => $result['summary'] ?? [],
                'broken_links_data' => $brokenLinksData,
                'total_checked' => $totalChecked,
                'total_broken' => $totalBroken,
                'raw_data' => json_encode($result),
            ]);

            \Log::info('Update result', [
                'check_id' => $checkRecord->id,
                'update_result' => $updateResult
            ]);

            // Verify the data was saved
            $checkRecord->refresh();
            \Log::info('Broken links data after save', [
                'check_id' => $checkRecord->id,
                'broken_links_data_count' => is_array($checkRecord->broken_links_data) ? count($checkRecord->broken_links_data) : 0,
                'broken_links_data_type' => gettype($checkRecord->broken_links_data),
                'total_broken' => $checkRecord->total_broken
            ]);

            // Double-check that status is updated (sometimes update() doesn't persist immediately)
            $checkRecord->refresh();
            if ($checkRecord->status !== 'completed') {
                \Log::warning('Status not updated to completed, forcing update', [
                    'check_id' => $checkRecord->id,
                    'current_status' => $checkRecord->status
                ]);
                $checkRecord->status = 'completed';
                $checkRecord->progress = 100;
                $checkRecord->save();
            }

            \Log::info('Broken Links Check Final Update', [
                'website_id' => $website->id,
                'check_id' => $checkRecord->id,
                'total_checked' => $totalChecked,
                'total_broken' => $totalBroken,
                'visited_urls_count' => count($result['broken_links'] ?? [])
            ]);

            // Create notification for broken links check completion
            try {
                NotificationService::notifyBrokenLinksCheckCompleted($website, $totalBroken, $totalChecked);
            } catch (\Exception $e) {
                \Log::warning('Failed to create notification for broken links check', [
                    'website_id' => $website->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Send email report if requested
            if ($sendEmail) {
                try {
                    $user = auth()->user();
                    if ($user && $user->email) {
                        \Illuminate\Support\Facades\Mail::to($user->email)
                            ->send(new \App\Mail\BrokenLinksReportMail($checkRecord->fresh(), $website->fresh()));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send broken links report email', [
                        'website_id' => $website->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            \Log::info('Broken Links Check Completed', [
                'website_id' => $website->id,
                'url' => $url,
                'total_checked' => $result['total_checked'] ?? 0,
                'total_broken' => $result['total_broken'] ?? 0
            ]);

        } catch (\Exception $e) {
            $checkRecord->update([
                'status' => 'failed',
                'progress' => 0
            ]);

            \Log::error('Background Broken Links Check Error', [
                'website_id' => $website->id,
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get progress for a broken links check
     */
    public function getBrokenLinksProgress(Website $website, Request $request)
    {
        $this->ensureWebsiteAccess($website->id);

        $checkId = $request->input('check_id');

        if ($checkId) {
            $check = BrokenLink::where('id', $checkId)
                ->where('website_id', $website->id)
                ->first();
        } else {
            $check = $website->brokenLinksChecks()
                ->whereIn('status', ['pending', 'running'])
                ->latest()
                ->first();
        }

        if (!$check) {
            return response()->json([
                'status' => 'not_found',
                'progress' => 0
            ]);
        }

        // Auto-complete stuck checks (running for more than 2 minutes OR stuck at 95% for more than 1 minute)
        if ($check->status === 'running') {
            $runningTime = $check->created_at->diffInSeconds(now());
            $isStuckAt95 = ($check->progress >= 95 && $runningTime > 60); // Stuck at 95% for 1 minute
            $isRunningTooLong = $runningTime > 120; // Running for more than 2 minutes

            if ($isStuckAt95 || $isRunningTooLong) {
                \Log::warning('Auto-completing stuck broken links check', [
                    'check_id' => $check->id,
                    'running_time_seconds' => $runningTime,
                    'current_progress' => $check->progress,
                    'total_checked' => $check->total_checked,
                    'reason' => $isStuckAt95 ? 'stuck_at_95' : 'running_too_long'
                ]);

                // Force completion with current values (at least mark initial URL as checked if 0)
                $totalChecked = $check->total_checked ?? 0;
                $totalBroken = $check->total_broken ?? 0;

                // If no pages were checked, mark the initial URL as checked
                if ($totalChecked === 0) {
                    $totalChecked = 1;
                }

                // Try to extract broken_links_data from raw_data if available
                $brokenLinksData = [];
                if ($check->raw_data) {
                    $rawData = json_decode($check->raw_data, true);
                    if (isset($rawData['broken_links']) && is_array($rawData['broken_links'])) {
                        $brokenLinksData = $rawData['broken_links'];
                    }
                }

                $check->update([
                    'status' => 'completed',
                    'progress' => 100,
                    'total_checked' => $totalChecked,
                    'total_broken' => $totalBroken,
                    'broken_links_data' => !empty($brokenLinksData) ? $brokenLinksData : null,
                ]);

                // Refresh to get updated values
                $check->refresh();
            }
        }

        return response()->json([
            'status' => $check->status,
            'progress' => $check->progress ?? 0,
            'total_checked' => $check->total_checked ?? 0,
            'total_broken' => $check->total_broken ?? 0,
            'check_id' => $check->id,
        ]);
    }

    /**
     * Show broken links results for a website
     */
    public function showBrokenLinks(Website $website)
    {
        $this->ensureWebsiteAccess($website->id);

        $latestCheck = $website->brokenLinksChecks()
            ->whereIn('status', ['completed', 'failed'])
            ->latest()
            ->first();

        // Only show progress if check is actually pending or running (not failed/cancelled)
        $activeCheck = $website->brokenLinksChecks()
            ->whereIn('status', ['pending', 'running'])
            ->latest()
            ->first();

        $allChecks = $website->brokenLinksChecks()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.websites.broken-links', compact('website', 'latestCheck', 'activeCheck', 'allChecks'));
    }

    /**
     * Run Domain Authority check for a website
     */
    public function runDomainAuthorityCheck(Website $website, Request $request)
    {
        $this->ensureWebsiteAccess($website->id);

        try {
            set_time_limit(180);
            ini_set('max_execution_time', 180);

            $url = $request->input('url', $website->url);
            $sendEmail = $request->has('send_email') && $request->input('send_email') == '1';

            $service = new DomainAuthorityService();
            $result = $service->runCheck($url);

            if (!$result) {
                return back()->with('error', 'Failed to run Domain Authority check. The API may be temporarily unavailable or the URL may be unreachable. Please try again.');
            }

            $domainAuthority =             $domainAuthority = DomainAuthority::create([
                'website_id' => $website->id,
                'domain_authority' => $result['domain_authority'] ?? null,
                'page_authority' => $result['page_authority'] ?? null,
                'spam_score' => $result['spam_score'] ?? null,
                'backlinks' => $result['backlinks'] ?? null,
                'referring_domains' => $result['referring_domains'] ?? null,
                'raw_data' => $result['raw_data'] ?? null,
            ]);

            // Create notification for Domain Authority check completion
            try {
                NotificationService::notifyDomainAuthorityCheckCompleted($website, $result['domain_authority'] ?? 0);
            } catch (\Exception $e) {
                \Log::warning('Failed to create notification for Domain Authority check', [
                    'website_id' => $website->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Send email report if requested
            if ($sendEmail) {
                try {
                    $user = auth()->user();
                    if ($user && $user->email) {
                        \Illuminate\Support\Facades\Mail::to($user->email)
                            ->send(new \App\Mail\DomainAuthorityReportMail($domainAuthority->fresh(), $website->fresh()));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send Domain Authority report email', [
                        'website_id' => $website->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return redirect()->route('admin.websites.domain-authority', $website)
                ->with('success', 'Domain Authority check completed successfully!');

        } catch (\Exception $e) {
            \Log::error('Domain Authority Check Error', [
                'website_id' => $website->id,
                'url' => $url ?? $website->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while running the Domain Authority check: ' . $e->getMessage());
        }
    }

    /**
     * Show Domain Authority results for a website
     */
    public function showDomainAuthority(Website $website)
    {
        $this->ensureWebsiteAccess($website->id);

        $latestCheck = $website->latestDomainAuthority();
        $allChecks = $website->domainAuthorities()
            ->orderBy('created_at', 'desc')
            ->get(); // Get all checks, not paginated for history

        // Group checks by month for chart (monthly averages)
        $monthlyData = $website->domainAuthorities()
            ->whereNotNull('domain_authority')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, AVG(domain_authority) as avg_da')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Prepare chart data from monthly averages
        $chartLabels = [];
        $chartData = [];
        foreach ($monthlyData as $data) {
            $chartLabels[] = $data->month;
            $chartData[] = round($data->avg_da);
        }

        // Also prepare individual check data for detailed history
        $historyData = $website->domainAuthorities()
            ->whereNotNull('domain_authority')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($check) {
                return [
                    'date' => $check->created_at->format('Y-m'),
                    'domain_authority' => $check->domain_authority,
                    'created_at' => $check->created_at
                ];
            });

        return view('admin.websites.domain-authority', compact('website', 'latestCheck', 'allChecks', 'monthlyData', 'chartLabels', 'chartData', 'historyData'));
    }
}
