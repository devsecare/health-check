<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broken Links Report - {{ $website->name }}</title>
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
            border-bottom: 3px solid #ea580c;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #ea580c;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-card {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
        }
        .summary-card.total {
            background-color: #f3f4f6;
        }
        .summary-card.broken {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
        }
        .summary-card.internal {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
        }
        .summary-card.external {
            background-color: #fff7ed;
            border-left: 4px solid #f97316;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
        }
        .summary-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #111827;
        }
        .summary-card.broken .value {
            color: #ef4444;
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
        .broken-links-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .broken-links-table th {
            background-color: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }
        .broken-links-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .broken-links-table tr:hover {
            background-color: #f9fafb;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.error {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-badge.warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .link-url {
            color: #2563eb;
            text-decoration: none;
            word-break: break-all;
        }
        .link-url:hover {
            text-decoration: underline;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .no-issues {
            text-align: center;
            padding: 40px;
            background-color: #f0fdf4;
            border-radius: 6px;
            border: 2px solid #22c55e;
        }
        .no-issues-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .no-issues h3 {
            color: #15803d;
            margin: 0 0 10px 0;
        }
        .no-issues p {
            color: #166534;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔗 Broken Links Report</h1>
            <p><strong>Website:</strong> {{ $website->name }} ({{ $website->url }})</p>
            <p><strong>Checked URL:</strong> {{ $check->url }}</p>
            <p><strong>Date:</strong> {{ $check->created_at->format('F d, Y H:i:s') }}</p>
        </div>

        <div class="summary">
            <div class="summary-card total">
                <h3>Total Checked</h3>
                <div class="value">{{ number_format($totalChecked) }}</div>
            </div>
            <div class="summary-card broken">
                <h3>Total Broken</h3>
                <div class="value">{{ number_format($totalBroken) }}</div>
            </div>
            <div class="summary-card internal">
                <h3>Internal Broken</h3>
                <div class="value">{{ number_format($summary['internal'] ?? 0) }}</div>
            </div>
            <div class="summary-card external">
                <h3>External Broken</h3>
                <div class="value">{{ number_format($summary['external'] ?? 0) }}</div>
            </div>
        </div>

        @if($totalBroken > 0)
            <div class="section">
                <h2>Broken Links Details</h2>
                <p>The following {{ min(50, count($brokenLinks)) }} broken links were found:</p>
                <table class="broken-links-table">
                    <thead>
                        <tr>
                            <th>Broken URL</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Found On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brokenLinks as $link)
                        <tr>
                            <td>
                                <a href="{{ $link['broken_url'] ?? $link['url'] ?? '#' }}" class="link-url" target="_blank">
                                    {{ \Illuminate\Support\Str::limit($link['broken_url'] ?? $link['url'] ?? 'N/A', 60) }}
                                </a>
                            </td>
                            <td>
                                <span class="status-badge {{ ($link['type'] ?? 'link') === 'link' ? 'warning' : 'error' }}">
                                    {{ ucfirst($link['type'] ?? 'link') }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $link['status_code'] ?? 'N/A' }}</strong>
                            </td>
                            <td style="font-size: 12px; color: #6b7280;">
                                {{ $link['found_on'] ? \Illuminate\Support\Str::limit($link['found_on'], 40) : 'N/A' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if(count($check->broken_links_data ?? []) > 50)
                    <p style="margin-top: 15px; color: #6b7280; font-size: 14px;">
                        <strong>Note:</strong> Showing first 50 of {{ count($check->broken_links_data ?? []) }} broken links. 
                        View the full report in the admin panel.
                    </p>
                @endif
            </div>

            @if(!empty($summary['by_type'] ?? []))
            <div class="section">
                <h2>Broken Links by Type</h2>
                <table class="broken-links-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['by_type'] as $type => $count)
                        <tr>
                            <td><strong>{{ ucfirst($type) }}</strong></td>
                            <td>{{ $count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(!empty($summary['by_status_code'] ?? []))
            <div class="section">
                <h2>Broken Links by Status Code</h2>
                <table class="broken-links-table">
                    <thead>
                        <tr>
                            <th>Status Code</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['by_status_code'] as $code => $count)
                        <tr>
                            <td><strong>HTTP {{ $code }}</strong></td>
                            <td>{{ $count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @else
            <div class="no-issues">
                <div class="no-issues-icon">✅</div>
                <h3>No Broken Links Found!</h3>
                <p>All {{ number_format($totalChecked) }} checked links are working properly.</p>
            </div>
        @endif

        <div class="footer">
            <p>This report was generated automatically by {{ config('app.name') }}</p>
            <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>

