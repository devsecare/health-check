<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Website;
use App\Models\PageSpeedInsight;
use App\Models\SeoAudit;
use App\Models\BrokenLink;
use App\Models\DomainAuthority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Get real statistics from database
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

        // Total Websites
        $totalWebsites = Website::count();
        $newWebsitesThisMonth = Website::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $websitesLastMonth = Website::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $websiteGrowth = $websitesLastMonth > 0
            ? round((($newWebsitesThisMonth - $websitesLastMonth) / $websitesLastMonth) * 100, 1)
            : ($newWebsitesThisMonth > 0 ? 100 : 0);

        // Total PageSpeed Tests
        $totalPageSpeedTests = PageSpeedInsight::count();
        $testsThisMonth = PageSpeedInsight::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $testsLastMonth = PageSpeedInsight::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $testsGrowth = $testsLastMonth > 0
            ? round((($testsThisMonth - $testsLastMonth) / $testsLastMonth) * 100, 1)
            : ($testsThisMonth > 0 ? 100 : 0);

        // Total SEO Audits
        $totalSeoAudits = SeoAudit::count();
        $seoAuditsThisMonth = SeoAudit::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $seoAuditsLastMonth = SeoAudit::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $seoGrowth = $seoAuditsLastMonth > 0
            ? round((($seoAuditsThisMonth - $seoAuditsLastMonth) / $seoAuditsLastMonth) * 100, 1)
            : ($seoAuditsThisMonth > 0 ? 100 : 0);

        // Total Broken Links Checks
        $totalBrokenLinksChecks = BrokenLink::where('status', 'completed')->count();
        $brokenLinksThisMonth = BrokenLink::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $brokenLinksLastMonth = BrokenLink::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $brokenLinksGrowth = $brokenLinksLastMonth > 0
            ? round((($brokenLinksThisMonth - $brokenLinksLastMonth) / $brokenLinksLastMonth) * 100, 1)
            : ($brokenLinksThisMonth > 0 ? 100 : 0);

        // Average Performance Score
        $avgPerformance = PageSpeedInsight::whereNotNull('performance_score')->avg('performance_score');

        // Activity data for last 7 days (combining all activities)
        $daysAgo = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));

        // PageSpeed tests per day
        $pageSpeedTestsLast7Days = PageSpeedInsight::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            });

        // SEO Audits per day
        $seoAuditsLast7Days = SeoAudit::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
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

        // Recent PageSpeed tests
        PageSpeedInsight::with('website')->latest()->take(3)->get()->each(function($test) use ($recentActivities) {
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

        // Recent SEO audits
        SeoAudit::with('website')->latest()->take(3)->get()->each(function($audit) use ($recentActivities) {
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

        // Recent broken links checks
        BrokenLink::with('website')->where('status', 'completed')->latest()->take(3)->get()->each(function($check) use ($recentActivities) {
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
        $users = User::latest()->paginate(10);
        return view('admin.users', compact('users'));
    }

    public function analytics()
    {
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

        // PageSpeed Insights Analytics
        $totalTests = PageSpeedInsight::count();
        $testsThisMonth = PageSpeedInsight::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalWebsites = Website::count();
        $websitesWithTests = Website::has('pageSpeedInsights')->count();

        // Average scores
        $avgPerformance = PageSpeedInsight::whereNotNull('performance_score')
            ->avg('performance_score');
        $avgAccessibility = PageSpeedInsight::whereNotNull('accessibility_score')
            ->avg('accessibility_score');
        $avgBestPractices = PageSpeedInsight::whereNotNull('best_practices_score')
            ->avg('best_practices_score');
        $avgSeo = PageSpeedInsight::whereNotNull('seo_score')
            ->avg('seo_score');

        // Average Core Web Vitals
        $avgLcp = PageSpeedInsight::whereNotNull('lcp')->avg('lcp');
        $avgFcp = PageSpeedInsight::whereNotNull('fcp')->avg('fcp');
        $avgCls = PageSpeedInsight::whereNotNull('cls')->avg('cls');

        // Performance trends by month
        $performanceByMonth = PageSpeedInsight::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('AVG(performance_score) as avg_score')
        )
            ->whereNotNull('performance_score')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recent tests
        $recentTests = PageSpeedInsight::with('website')
            ->latest()
            ->take(10)
            ->get();

        // Top performing websites (by performance score)
        $topWebsites = Website::select('websites.*')
            ->joinSub(
                PageSpeedInsight::select('website_id')
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

        // SEO Audit Analytics
        $totalSeoAudits = SeoAudit::count();
        $seoAuditsThisMonth = SeoAudit::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $websitesWithSeoAudits = Website::has('seoAudits')->count();

        // Average SEO score
        $avgSeoAuditScore = SeoAudit::whereNotNull('overall_score')
            ->avg('overall_score');

        // SEO trends by month
        $seoAuditsByMonth = SeoAudit::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('AVG(overall_score) as avg_score')
        )
            ->whereNotNull('overall_score')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recent SEO audits
        $recentSeoAudits = SeoAudit::with('website')
            ->latest()
            ->take(10)
            ->get();

        // Top SEO websites
        $topSeoWebsites = Website::select('websites.*')
            ->joinSub(
                SeoAudit::select('website_id')
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

        // Broken Links Analytics
        $totalBrokenLinksChecks = BrokenLink::count();
        $brokenLinksChecksThisMonth = BrokenLink::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $websitesWithBrokenLinksChecks = Website::has('brokenLinksChecks')->count();

        // Total broken links found
        $totalBrokenLinksFound = BrokenLink::where('status', 'completed')
            ->sum('total_broken');

        // Total pages checked
        $totalPagesChecked = BrokenLink::where('status', 'completed')
            ->sum('total_checked');

        // Average broken links per check (calculated in PHP for better compatibility)
        $completedChecks = BrokenLink::where('status', 'completed')
            ->where('total_checked', '>', 0)
            ->get();
        $avgBrokenLinksPerCheck = $completedChecks->count() > 0
            ? $completedChecks->avg(function($check) {
                return $check->total_checked > 0 ? ($check->total_broken / $check->total_checked) : 0;
            })
            : 0;

        // Broken links trends by month
        $brokenLinksByMonth = BrokenLink::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_broken) as total_broken'),
            DB::raw('SUM(total_checked) as total_checked')
        )
            ->where('status', 'completed')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recent broken links checks
        $recentBrokenLinksChecks = BrokenLink::with('website')
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get();

        // Websites with most broken links
        $websitesWithMostBrokenLinks = Website::select('websites.*')
            ->joinSub(
                BrokenLink::select('website_id')
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

        // Domain Authority Analytics
        $totalDomainAuthorityChecks = DomainAuthority::count();
        $domainAuthorityChecksThisMonth = DomainAuthority::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $websitesWithDomainAuthority = Website::has('domainAuthorities')->count();

        // Average Domain Authority
        $avgDomainAuthority = DomainAuthority::whereNotNull('domain_authority')
            ->avg('domain_authority');
        $avgPageAuthority = DomainAuthority::whereNotNull('page_authority')
            ->avg('page_authority');

        // Total backlinks and referring domains
        $totalBacklinks = DomainAuthority::whereNotNull('backlinks')
            ->sum('backlinks');
        $totalReferringDomains = DomainAuthority::whereNotNull('referring_domains')
            ->sum('referring_domains');

        // Domain Authority trends by month
        $domainAuthorityByMonth = DomainAuthority::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('AVG(domain_authority) as avg_domain_authority')
        )
            ->whereNotNull('domain_authority')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recent domain authority checks
        $recentDomainAuthorityChecks = DomainAuthority::with('website')
            ->latest()
            ->take(10)
            ->get();

        // Top websites by Domain Authority
        $topDomainAuthorityWebsites = Website::select('websites.*')
            ->joinSub(
                DomainAuthority::select('website_id')
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
        return view('admin.settings');
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
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

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    public function deleteUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }
}
