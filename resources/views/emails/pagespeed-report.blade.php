<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PageSpeed Insights Report - {{ $website->name }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 28px;
        }
        .scores {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .score-card {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
        }
        .score-card.performance { border-left: 4px solid #2563eb; }
        .score-card.accessibility { border-left: 4px solid #22c55e; }
        .score-card.seo { border-left: 4px solid #9333ea; }
        .score-card.best-practices { border-left: 4px solid #f59e0b; }
        .score-card h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
        }
        .score-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #111827;
        }
        .metrics {
            margin-bottom: 30px;
        }
        .metrics h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #111827;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .metric-item {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .metric-item:last-child {
            border-bottom: none;
        }
        .metric-label {
            font-weight: 600;
            color: #374151;
        }
        .metric-value {
            color: #111827;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚡ PageSpeed Insights Report</h1>
            <p><strong>Website:</strong> {{ $website->name }} ({{ $website->url }})</p>
            <p><strong>Strategy:</strong> {{ ucfirst($insight->strategy ?? 'mobile') }}</p>
            <p><strong>Date:</strong> {{ $insight->created_at->format('F d, Y H:i:s') }}</p>
        </div>

        <div class="scores">
            <div class="score-card performance">
                <h3>Performance</h3>
                <div class="value">{{ $insight->performance_score ?? 0 }}/100</div>
            </div>
            <div class="score-card accessibility">
                <h3>Accessibility</h3>
                <div class="value">{{ $insight->accessibility_score ?? 0 }}/100</div>
            </div>
            <div class="score-card seo">
                <h3>SEO</h3>
                <div class="value">{{ $insight->seo_score ?? 0 }}/100</div>
            </div>
            <div class="score-card best-practices">
                <h3>Best Practices</h3>
                <div class="value">{{ $insight->best_practices_score ?? 0 }}/100</div>
            </div>
        </div>

        <div class="metrics">
            <h2>Core Web Vitals</h2>
            <div class="metric-item">
                <span class="metric-label">Largest Contentful Paint (LCP)</span>
                <span class="metric-value">{{ number_format($insight->lcp ?? 0, 2) }}s</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">First Contentful Paint (FCP)</span>
                <span class="metric-value">{{ number_format($insight->fcp ?? 0, 2) }}s</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Cumulative Layout Shift (CLS)</span>
                <span class="metric-value">{{ number_format($insight->cls ?? 0, 3) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Total Blocking Time (TBT)</span>
                <span class="metric-value">{{ number_format($insight->tbt ?? 0, 2) }}ms</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Speed Index (SI)</span>
                <span class="metric-value">{{ number_format($insight->si ?? 0, 2) }}s</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Time to First Byte (TTFB)</span>
                <span class="metric-value">{{ number_format($insight->ttfb ?? 0, 2) }}ms</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Time to Interactive (TTI)</span>
                <span class="metric-value">{{ number_format($insight->interactive ?? 0, 2) }}s</span>
            </div>
        </div>

        <div class="footer">
            <p>This report was generated automatically by {{ config('app.name') }}</p>
            <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
