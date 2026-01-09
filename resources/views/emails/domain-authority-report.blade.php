<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Authority Report - {{ $website->name }}</title>
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
        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .metric-card {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            border-left: 4px solid #2563eb;
        }
        .metric-card h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
        }
        .metric-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #111827;
        }
        .metric-card .label {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
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
        .info-item {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }
        .info-item:last-child {
            border-bottom: none;
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
            <h1>🔗 Domain Authority Report</h1>
            <p><strong>Website:</strong> {{ $website->name }} ({{ $website->url }})</p>
            <p><strong>Date:</strong> {{ $domainAuthority->created_at->format('F d, Y H:i:s') }}</p>
        </div>

        <div class="metrics">
            <div class="metric-card">
                <h3>Domain Authority</h3>
                <div class="value">{{ $domainAuthority->domain_authority ?? 'N/A' }}</div>
                <div class="label">Out of 100</div>
            </div>
            <div class="metric-card">
                <h3>Page Authority</h3>
                <div class="value">{{ $domainAuthority->page_authority ?? 'N/A' }}</div>
                <div class="label">Out of 100</div>
            </div>
            <div class="metric-card">
                <h3>Spam Score</h3>
                <div class="value">{{ $domainAuthority->spam_score ?? 'N/A' }}</div>
                <div class="label">Lower is better</div>
            </div>
        </div>

        <div class="section">
            <h2>Link Metrics</h2>
            @if($domainAuthority->backlinks)
            <div class="info-item">
                <span><strong>Total Backlinks:</strong></span>
                <span>{{ number_format($domainAuthority->backlinks) }}</span>
            </div>
            @endif
            @if($domainAuthority->referring_domains)
            <div class="info-item">
                <span><strong>Referring Domains:</strong></span>
                <span>{{ number_format($domainAuthority->referring_domains) }}</span>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>This report was generated automatically by {{ config('app.name') }}</p>
            <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
