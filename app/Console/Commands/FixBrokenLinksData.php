<?php

namespace App\Console\Commands;

use App\Models\BrokenLink;
use Illuminate\Console\Command;

class FixBrokenLinksData extends Command
{
    protected $signature = 'broken-links:fix-data {check_id?}';
    protected $description = 'Fix broken links data for checks that have total_broken > 0 but no broken_links_data';

    public function handle()
    {
        $checkId = $this->argument('check_id');
        
        if ($checkId) {
            $checks = BrokenLink::where('id', $checkId)->get();
        } else {
            $checks = BrokenLink::where('total_broken', '>', 0)
                ->where(function($query) {
                    $query->whereNull('broken_links_data')
                          ->orWhereRaw('JSON_LENGTH(broken_links_data) = 0');
                })
                ->get();
        }
        
        if ($checks->isEmpty()) {
            $this->info('No checks need fixing.');
            return 0;
        }
        
        $this->info("Found {$checks->count()} check(s) to fix.");
        
        foreach ($checks as $check) {
            $this->info("Processing check ID: {$check->id}");
            
            // Try to extract from raw_data
            if (!empty($check->raw_data)) {
                $rawData = json_decode($check->raw_data, true);
                if (isset($rawData['broken_links']) && is_array($rawData['broken_links'])) {
                    $check->broken_links_data = $rawData['broken_links'];
                    $check->save();
                    $this->info("  ✓ Fixed from raw_data: " . count($rawData['broken_links']) . " links");
                    continue;
                }
            }
            
            // If no raw_data, we can't recover the data
            $this->warn("  ✗ No raw_data available for check {$check->id}. Cannot recover broken links data.");
        }
        
        $this->info('Done!');
        return 0;
    }
}

