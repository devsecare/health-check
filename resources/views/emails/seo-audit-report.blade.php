<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Audit Report - {{ $website->name }}</title>
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
            border-bottom: 3px solid #9333ea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #9333ea;
            font-size: 28px;
        }
        .overall-score {
            text-align: center;
            padding: 30px;
            background-color: #f9fafb;
            border-radius: 6px;
            margin-bottom: 30px;
        }
        .overall-score .score {
            font-size: 64px;
            font-weight: bold;
            color: #9333ea;
            margin: 10px 0;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #111827;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .check-item {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.pass {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-badge.fail {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-badge.warning {
            background-color: #fef3c7;
            color: #92400e;
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
            <h1>🔍 SEO Audit Report</h1>
            <p><strong>Website:</strong> {{ $website->name }} ({{ $website->url }})</p>
            <p><strong>Audited URL:</strong> {{ $audit->url }}</p>
            <p><strong>Date:</strong> {{ $audit->created_at->format('F d, Y H:i:s') }}</p>
        </div>

        <div class="overall-score">
            <h3 style="margin: 0; color: #6b7280; font-size: 14px; text-transform: uppercase;">Overall SEO Score</h3>
            <div class="score">{{ $audit->overall_score ?? 0 }}/100</div>
        </div>

        @php
            $metaTags = $audit->meta_tags ?? [];
            $headings = $audit->headings ?? [];
            $images = $audit->images ?? [];
        @endphp

        @if(!empty($metaTags))
        <div class="section">
            <h2>Meta Tags</h2>
            @if(isset($metaTags['title']))
            <div class="check-item">
                <span><strong>Title Tag:</strong> {{ $metaTags['title']['exists'] ? 'Present' : 'Missing' }}</span>
                <span class="status-badge {{ $metaTags['title']['exists'] ? 'pass' : 'fail' }}">
                    {{ $metaTags['title']['exists'] ? 'Pass' : 'Fail' }}
                </span>
            </div>
            @endif
            @if(isset($metaTags['description']))
            <div class="check-item">
                <span><strong>Meta Description:</strong> {{ $metaTags['description']['exists'] ? 'Present' : 'Missing' }}</span>
                <span class="status-badge {{ $metaTags['description']['exists'] ? 'pass' : 'fail' }}">
                    {{ $metaTags['description']['exists'] ? 'Pass' : 'Fail' }}
                </span>
            </div>
            @endif
        </div>
        @endif

        @if(!empty($headings))
        <div class="section">
            <h2>Headings Structure</h2>
            @if(isset($headings['h1_count']))
            <div class="check-item">
                <span><strong>H1 Tags:</strong> {{ $headings['h1_count'] ?? 0 }} found</span>
                <span class="status-badge {{ ($headings['h1_count'] ?? 0) == 1 ? 'pass' : (($headings['h1_count'] ?? 0) == 0 ? 'fail' : 'warning') }}">
                    {{ ($headings['h1_count'] ?? 0) == 1 ? 'Pass' : (($headings['h1_count'] ?? 0) == 0 ? 'Fail' : 'Warning') }}
                </span>
            </div>
            @endif
        </div>
        @endif

        @if(!empty($images))
        <div class="section">
            <h2>Images</h2>
            @if(isset($images['total']))
            <div class="check-item">
                <span><strong>Total Images:</strong> {{ $images['total'] ?? 0 }}</span>
            </div>
            @endif
            @if(isset($images['with_alt']))
            <div class="check-item">
                <span><strong>Images with Alt Text:</strong> {{ $images['with_alt'] ?? 0 }}</span>
                <span class="status-badge {{ ($images['with_alt'] ?? 0) == ($images['total'] ?? 0) ? 'pass' : 'warning' }}">
                    {{ ($images['with_alt'] ?? 0) == ($images['total'] ?? 0) ? 'Pass' : 'Warning' }}
                </span>
            </div>
            @endif
        </div>
        @endif

        <div class="footer">
            <p>This report was generated automatically by {{ config('app.name') }}</p>
            <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
