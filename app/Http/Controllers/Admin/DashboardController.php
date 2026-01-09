<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Website;
use App\Models\PageSpeedInsight;
use App\Models\SeoAudit;
use App\Models\BrokenLink;
use App\Models\DomainAuthority;
use App\Traits\HasWebsiteAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use HasWebsiteAccess;
    public function dashboard()
    {
        $user = auth()->user();
        $accessibleWebsiteIds = $this->getAccessibleWebsiteIds();

        // Get real statistics from database
        // Only super admin can see all users
        if ($user->isSuperAdmin()) {
            $totalUsers = User::count();
            $newUsersThisMonth = User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $usersLastMonth = User::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();

            $userGrowth = $usersLastMonth > 0
                ? round((($newUsersThisMonth - $usersLastMonth) / $usersLastMonth) * 100, 1)
                : ($newUsersThisMonth > 0 ? 100 : 0);
        } else {
            $totalUsers = 0;
            $newUsersThisMonth = 0;
            $usersLastMonth = 0;
            $userGrowth = 0;
        }

        // Total Websites - filtered by access
        $totalWebsites = Website::whereIn('id', $accessibleWebsiteIds)->count();
        $newWebsitesThisMonth = Website::whereIn('id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $websitesLastMonth = Website::whereIn('id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $websiteGrowth = $websitesLastMonth > 0
            ? round((($newWebsitesThisMonth - $websitesLastMonth) / $websitesLastMonth) * 100, 1)
            : ($newWebsitesThisMonth > 0 ? 100 : 0);

        // Total PageSpeed Tests - filtered by accessible websites
        $totalPageSpeedTests = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)->count();
        $testsThisMonth = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $testsLastMonth = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $testsGrowth = $testsLastMonth > 0
            ? round((($testsThisMonth - $testsLastMonth) / $testsLastMonth) * 100, 1)
            : ($testsThisMonth > 0 ? 100 : 0);

        // Total SEO Audits - filtered by accessible websites
        $totalSeoAudits = SeoAudit::whereIn('website_id', $accessibleWebsiteIds)->count();
        $seoAuditsThisMonth = SeoAudit::whereIn('website_id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $seoAuditsLastMonth = SeoAudit::whereIn('website_id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $seoGrowth = $seoAuditsLastMonth > 0
            ? round((($seoAuditsThisMonth - $seoAuditsLastMonth) / $seoAuditsLastMonth) * 100, 1)
            : ($seoAuditsThisMonth > 0 ? 100 : 0);

        // Total Broken Links Checks - filtered by accessible websites
        $totalBrokenLinksChecks = BrokenLink::whereIn('website_id', $accessibleWebsiteIds)
            ->where('status', 'completed')->count();
        $brokenLinksThisMonth = BrokenLink::whereIn('website_id', $accessibleWebsiteIds)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $brokenLinksLastMonth = BrokenLink::whereIn('website_id', $accessibleWebsiteIds)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $brokenLinksGrowth = $brokenLinksLastMonth > 0
            ? round((($brokenLinksThisMonth - $brokenLinksLastMonth) / $brokenLinksLastMonth) * 100, 1)
            : ($brokenLinksThisMonth > 0 ? 100 : 0);

        // Average Performance Score - filtered by accessible websites
        $avgPerformance = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('performance_score')->avg('performance_score');

        // Activity data for last 7 days (combining all activities)
        $daysAgo = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));

        // PageSpeed tests per day - filtered by accessible websites
        $pageSpeedTestsLast7Days = PageSpeedInsight::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            });

        // SEO Audits per day - filtered by accessible websites
        $seoAuditsLast7Days = SeoAudit::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            });

        // Combined activity chart data
        $activityLast7Days = $daysAgo->map(function($date) use ($pageSpeedTestsLast7Days, $seoAuditsLast7Days) {
            return [
                'date' => $date,
                'pageSpeed' => $pageSpeedTestsLast7Days->get($date, 0),
                'seo' => $seoAuditsLast7Days->get($date, 0),
                'total' => ($pageSpeedTestsLast7Days->get($date, 0) + $seoAuditsLast7Days->get($date, 0))
            ];
        });

        // Recent activity - combine all recent activities
        $recentActivities = collect();

        // Recent PageSpeed tests - filtered by accessible websites
        PageSpeedInsight::with('website')
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->latest()->take(3)->get()->each(function($test) use ($recentActivities) {
                $recentActivities->push([
                    'type' => 'pagespeed',
                    'icon' => 'bolt',
                    'iconColor' => 'blue',
                    'title' => 'PageSpeed test completed',
                    'description' => $test->website->name ?? 'Unknown website',
                    'score' => $test->performance_score ?? 0,
                    'time' => $test->created_at,
                    'url' => route('admin.websites.pagespeed', $test->website_id)
                ]);
            });

        // Recent SEO audits - filtered by accessible websites
        SeoAudit::with('website')
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->latest()->take(3)->get()->each(function($audit) use ($recentActivities) {
                $recentActivities->push([
                    'type' => 'seo',
                    'icon' => 'search',
                    'iconColor' => 'purple',
                    'title' => 'SEO audit completed',
                    'description' => $audit->website->name ?? 'Unknown website',
                    'score' => $audit->overall_score ?? 0,
                    'time' => $audit->created_at,
                    'url' => route('admin.websites.seo-audit', $audit->website_id)
                ]);
            });

        // Recent broken links checks - filtered by accessible websites
        BrokenLink::with('website')
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->where('status', 'completed')->latest()->take(3)->get()->each(function($check) use ($recentActivities) {
                $recentActivities->push([
                    'type' => 'broken-links',
                    'icon' => 'link',
                    'iconColor' => 'orange',
                    'title' => 'Broken links check completed',
                    'description' => $check->website->name ?? 'Unknown website',
                    'score' => $check->total_broken ?? 0,
                    'time' => $check->created_at,
                    'url' => route('admin.websites.broken-links', $check->website_id)
                ]);
            });

        // Sort by time and take top 10
        $recentActivities = $recentActivities->sortByDesc('time')->take(10)->values();

        return view('admin.dashboard', compact(
            'totalUsers',
            'newUsersThisMonth',
            'userGrowth',
            'totalWebsites',
            'newWebsitesThisMonth',
            'websiteGrowth',
            'totalPageSpeedTests',
            'testsThisMonth',
            'testsGrowth',
            'totalSeoAudits',
            'seoAuditsThisMonth',
            'seoGrowth',
            'totalBrokenLinksChecks',
            'brokenLinksThisMonth',
            'brokenLinksGrowth',
            'avgPerformance',
            'activityLast7Days',
            'recentActivities'
        ));
    }

    public function users()
    {
        $user = auth()->user();

        // Only super admin can see all users
        if ($user->isSuperAdmin()) {
            $users = User::latest()->paginate(10);
        } else {
            // Regular users cannot access user management
            abort(403, 'You do not have permission to view users.');
        }

        return view('admin.users', compact('users'));
    }

    public function createUser()
    {
        $user = auth()->user();

        // Only super admin can create users
        if (!$user->isSuperAdmin()) {
            abort(403, 'You do not have permission to create users.');
        }

        $websites = Website::all();
        return view('admin.users.create', compact('websites'));
    }

    public function storeUser(Request $request)
    {
        $currentUser = auth()->user();

        // Only super admin can create users
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'You do not have permission to create users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:super_admin,user',
            'website_limit' => 'nullable|integer|min:0',
            'websites' => 'nullable|array',
            'websites.*' => 'exists:websites,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'website_limit' => $validated['website_limit'] ?? null,
        ]);

        // Sync website assignments (only for regular users)
        if ($user->role === 'user' && isset($validated['websites'])) {
            $user->websites()->sync($validated['websites']);
        }

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    public function analytics()
    {
        $user = auth()->user();
        $accessibleWebsiteIds = $this->getAccessibleWebsiteIds();

        // User analytics - only for super admin
        if ($user->isSuperAdmin()) {
            $totalUsers = User::count();
            $usersThisMonth = User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $usersByMonth = User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
        } else {
            $totalUsers = 0;
            $usersThisMonth = 0;
            $usersByMonth = collect();
        }

        // PageSpeed Insights Analytics - filtered by accessible websites
        $totalTests = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)->count();
        $testsThisMonth = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalWebsites = Website::whereIn('id', $accessibleWebsiteIds)->count();
        $websitesWithTests = Website::whereIn('id', $accessibleWebsiteIds)
            ->has('pageSpeedInsights')
            ->count();

        // Average scores - filtered by accessible websites
        $avgPerformance = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('performance_score')
            ->avg('performance_score');
        $avgAccessibility = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('accessibility_score')
            ->avg('accessibility_score');
        $avgBestPractices = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('best_practices_score')
            ->avg('best_practices_score');
        $avgSeo = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('seo_score')
            ->avg('seo_score');

        // Average Core Web Vitals - filtered by accessible websites
        $avgLcp = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('lcp')->avg('lcp');
        $avgFcp = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('fcp')->avg('fcp');
        $avgCls = PageSpeedInsight::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('cls')->avg('cls');

        // Performance trends by month - filtered by accessible websites
        $performanceByMonth = PageSpeedInsight::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('AVG(performance_score) as avg_score')
        )
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('performance_score')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recent tests - filtered by accessible websites
        $recentTests = PageSpeedInsight::with('website')
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->latest()
            ->take(10)
            ->get();

        // Top performing websites (by performance score) - filtered by accessible websites
        $topWebsites = Website::whereIn('websites.id', $accessibleWebsiteIds)
            ->select('websites.*')
            ->joinSub(
                PageSpeedInsight::select('website_id')
                    ->whereIn('website_id', $accessibleWebsiteIds)
                    ->selectRaw('AVG(performance_score) as avg_performance')
                    ->whereNotNull('performance_score')
                    ->groupBy('website_id'),
                'avg_scores',
                'websites.id',
                '=',
                'avg_scores.website_id'
            )
            ->addSelect('avg_scores.avg_performance')
            ->orderByDesc('avg_scores.avg_performance')
            ->take(5)
            ->get();

        // SEO Audit Analytics - filtered by accessible websites
        $totalSeoAudits = SeoAudit::whereIn('website_id', $accessibleWebsiteIds)->count();
        $seoAuditsThisMonth = SeoAudit::whereIn('website_id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $websitesWithSeoAudits = Website::whereIn('id', $accessibleWebsiteIds)
            ->has('seoAudits')
            ->count();

        // Average SEO score - filtered by accessible websites
        $avgSeoAuditScore = SeoAudit::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('overall_score')
            ->avg('overall_score');

        // SEO trends by month - filtered by accessible websites
        $seoAuditsByMonth = SeoAudit::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('AVG(overall_score) as avg_score')
        )
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('overall_score')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recent SEO audits - filtered by accessible websites
        $recentSeoAudits = SeoAudit::with('website')
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->latest()
            ->take(10)
            ->get();

        // Top SEO websites - filtered by accessible websites
        $topSeoWebsites = Website::whereIn('websites.id', $accessibleWebsiteIds)
            ->select('websites.*')
            ->joinSub(
                SeoAudit::select('website_id')
                    ->whereIn('website_id', $accessibleWebsiteIds)
                    ->selectRaw('AVG(overall_score) as avg_seo_score')
                    ->whereNotNull('overall_score')
                    ->groupBy('website_id'),
                'avg_seo_scores',
                'websites.id',
                '=',
                'avg_seo_scores.website_id'
            )
            ->addSelect('avg_seo_scores.avg_seo_score')
            ->orderByDesc('avg_seo_scores.avg_seo_score')
            ->take(5)
            ->get();

        // Broken Links Analytics - filtered by accessible websites
        $totalBrokenLinksChecks = BrokenLink::whereIn('website_id', $accessibleWebsiteIds)->count();
        $brokenLinksChecksThisMonth = BrokenLink::whereIn('website_id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $websitesWithBrokenLinksChecks = Website::whereIn('id', $accessibleWebsiteIds)
            ->has('brokenLinksChecks')
            ->count();

        // Total broken links found - filtered by accessible websites
        $totalBrokenLinksFound = BrokenLink::whereIn('website_id', $accessibleWebsiteIds)
            ->where('status', 'completed')
            ->sum('total_broken');

        // Total pages checked - filtered by accessible websites
        $totalPagesChecked = BrokenLink::whereIn('website_id', $accessibleWebsiteIds)
            ->where('status', 'completed')
            ->sum('total_checked');

        // Average broken links per check (calculated in PHP for better compatibility) - filtered by accessible websites
        $completedChecks = BrokenLink::whereIn('website_id', $accessibleWebsiteIds)
            ->where('status', 'completed')
            ->where('total_checked', '>', 0)
            ->get();
        $avgBrokenLinksPerCheck = $completedChecks->count() > 0
            ? $completedChecks->avg(function($check) {
                return $check->total_checked > 0 ? ($check->total_broken / $check->total_checked) : 0;
            })
            : 0;

        // Broken links trends by month - filtered by accessible websites
        $brokenLinksByMonth = BrokenLink::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_broken) as total_broken'),
            DB::raw('SUM(total_checked) as total_checked')
        )
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->where('status', 'completed')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recent broken links checks - filtered by accessible websites
        $recentBrokenLinksChecks = BrokenLink::with('website')
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get();

        // Websites with most broken links - filtered by accessible websites
        $websitesWithMostBrokenLinks = Website::whereIn('websites.id', $accessibleWebsiteIds)
            ->select('websites.*')
            ->joinSub(
                BrokenLink::select('website_id')
                    ->whereIn('website_id', $accessibleWebsiteIds)
                    ->selectRaw('SUM(total_broken) as total_broken_links')
                    ->where('status', 'completed')
                    ->whereNotNull('total_broken')
                    ->groupBy('website_id'),
                'broken_links_totals',
                'websites.id',
                '=',
                'broken_links_totals.website_id'
            )
            ->addSelect('broken_links_totals.total_broken_links')
            ->orderByDesc('broken_links_totals.total_broken_links')
            ->take(5)
            ->get();

        // Domain Authority Analytics - filtered by accessible websites
        $totalDomainAuthorityChecks = DomainAuthority::whereIn('website_id', $accessibleWebsiteIds)->count();
        $domainAuthorityChecksThisMonth = DomainAuthority::whereIn('website_id', $accessibleWebsiteIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $websitesWithDomainAuthority = Website::whereIn('id', $accessibleWebsiteIds)
            ->has('domainAuthorities')
            ->count();

        // Average Domain Authority - filtered by accessible websites
        $avgDomainAuthority = DomainAuthority::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('domain_authority')
            ->avg('domain_authority');
        $avgPageAuthority = DomainAuthority::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('page_authority')
            ->avg('page_authority');

        // Total backlinks and referring domains - filtered by accessible websites
        $totalBacklinks = DomainAuthority::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('backlinks')
            ->sum('backlinks');
        $totalReferringDomains = DomainAuthority::whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('referring_domains')
            ->sum('referring_domains');

        // Domain Authority trends by month - filtered by accessible websites
        $domainAuthorityByMonth = DomainAuthority::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('AVG(domain_authority) as avg_domain_authority')
        )
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->whereNotNull('domain_authority')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recent domain authority checks - filtered by accessible websites
        $recentDomainAuthorityChecks = DomainAuthority::with('website')
            ->whereIn('website_id', $accessibleWebsiteIds)
            ->latest()
            ->take(10)
            ->get();

        // Top websites by Domain Authority - filtered by accessible websites
        $topDomainAuthorityWebsites = Website::whereIn('websites.id', $accessibleWebsiteIds)
            ->select('websites.*')
            ->joinSub(
                DomainAuthority::select('website_id')
                    ->whereIn('website_id', $accessibleWebsiteIds)
                    ->selectRaw('AVG(domain_authority) as avg_domain_authority')
                    ->whereNotNull('domain_authority')
                    ->groupBy('website_id'),
                'da_avg',
                'websites.id',
                '=',
                'da_avg.website_id'
            )
            ->addSelect('da_avg.avg_domain_authority')
            ->orderByDesc('da_avg.avg_domain_authority')
            ->take(5)
            ->get();

        return view('admin.analytics', compact(
            'totalUsers',
            'usersThisMonth',
            'usersByMonth',
            'totalTests',
            'testsThisMonth',
            'totalWebsites',
            'websitesWithTests',
            'avgPerformance',
            'avgAccessibility',
            'avgBestPractices',
            'avgSeo',
            'avgLcp',
            'avgFcp',
            'avgCls',
            'performanceByMonth',
            'recentTests',
            'topWebsites',
            // SEO Audit data
            'totalSeoAudits',
            'seoAuditsThisMonth',
            'websitesWithSeoAudits',
            'avgSeoAuditScore',
            'seoAuditsByMonth',
            'recentSeoAudits',
            'topSeoWebsites',
            // Broken Links data
            'totalBrokenLinksChecks',
            'brokenLinksChecksThisMonth',
            'websitesWithBrokenLinksChecks',
            'totalBrokenLinksFound',
            'totalPagesChecked',
            'avgBrokenLinksPerCheck',
            'brokenLinksByMonth',
            'recentBrokenLinksChecks',
            'websitesWithMostBrokenLinks',
            // Domain Authority data
            'totalDomainAuthorityChecks',
            'domainAuthorityChecksThisMonth',
            'websitesWithDomainAuthority',
            'avgDomainAuthority',
            'avgPageAuthority',
            'totalBacklinks',
            'totalReferringDomains',
            'domainAuthorityByMonth',
            'recentDomainAuthorityChecks',
            'topDomainAuthorityWebsites'
        ));
    }

    public function settings()
    {
        $user = auth()->user();

        // Only super admin can access settings
        if (!$user->isSuperAdmin()) {
            abort(403, 'You do not have permission to access settings.');
        }

        return view('admin.settings');
    }

    public function profile()
    {
        $user = auth()->user();
        $user->load('websites');
        $websites = Website::all();

        return view('admin.profile', compact('user', 'websites'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully!');
    }

    public function editUser(User $user)
    {
        $currentUser = auth()->user();

        // Only super admin can edit users
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'You do not have permission to edit users.');
        }

        $websites = Website::all();
        $user->load('websites');

        return view('admin.users.edit', compact('user', 'websites'));
    }

    public function updateUser(Request $request, User $user)
    {
        $currentUser = auth()->user();

        // Only super admin can update users
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'You do not have permission to update users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'role' => 'required|in:super_admin,user',
            'website_limit' => 'nullable|integer|min:0',
            'websites' => 'nullable|array',
            'websites.*' => 'exists:websites,id',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->website_limit = $validated['website_limit'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        // Sync website assignments (only for regular users)
        if ($user->role === 'user' && isset($validated['websites'])) {
            $user->websites()->sync($validated['websites']);
        } elseif ($user->role === 'super_admin') {
            // Super admin doesn't need website assignments
            $user->websites()->detach();
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    public function deleteUser(User $user)
    {
        $currentUser = auth()->user();

        // Only super admin can delete users
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'You do not have permission to delete users.');
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }
}
