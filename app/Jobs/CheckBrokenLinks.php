<?php

namespace App\Jobs;

use App\Models\Website;
use App\Models\BrokenLink;
use App\Services\BrokenLinksService;
use App\Mail\BrokenLinksReportMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckBrokenLinks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180; // 3 minutes (optimized for faster results)
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Website $website,
        public BrokenLink $checkRecord,
        public string $url,
        public int $maxDepth = 2,
        public int $maxPages = 30,
        public bool $sendEmail = false
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status to running
            $this->checkRecord->update([
                'status' => 'running',
                'progress' => 5
            ]);

            // Set execution time limit for the job
            set_time_limit($this->timeout);
            ini_set('max_execution_time', $this->timeout);
            
            // Create service with progress callback
            $service = new BrokenLinksService();
            $checkRecord = $this->checkRecord;
            $service->setProgressCallback(function($progress, $message = '') use ($checkRecord) {
                $checkRecord->update([
                    'progress' => min(100, max(0, $progress))
                ]);
            });

            $result = $service->runCheck($this->url, $this->maxDepth, $this->maxPages);

            if (!$result) {
                $this->checkRecord->update([
                    'status' => 'failed',
                    'progress' => 0
                ]);
                Log::error('Broken Links Check: No result returned', [
                    'website_id' => $this->website->id,
                    'url' => $this->url
                ]);
                return;
            }

            // Update with final results
            $this->checkRecord->update([
                'status' => 'completed',
                'progress' => 100,
                'summary' => $result['summary'] ?? [],
                'broken_links_data' => $result['broken_links'] ?? [],
                'total_checked' => $result['total_checked'] ?? 0,
                'total_broken' => $result['total_broken'] ?? 0,
                'raw_data' => json_encode($result),
            ]);

            // Send email report if requested
            if ($this->sendEmail) {
                try {
                    // Get the first user's email (in a real app, you'd get the user who initiated the check)
                    $user = \App\Models\User::first();
                    if ($user && $user->email) {
                        Mail::to($user->email)->send(new BrokenLinksReportMail($this->checkRecord->fresh(), $this->website->fresh()));
                        Log::info('Broken Links Report Email Sent', [
                            'website_id' => $this->website->id,
                            'email' => $user->email
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to send broken links report email', [
                        'website_id' => $this->website->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Broken Links Check Completed', [
                'website_id' => $this->website->id,
                'url' => $this->url,
                'total_checked' => $result['total_checked'] ?? 0,
                'total_broken' => $result['total_broken'] ?? 0
            ]);
                
        } catch (\Exception $e) {
            $this->checkRecord->update([
                'status' => 'failed',
                'progress' => 0
            ]);

            Log::error('Broken Links Check Job Error', [
                'website_id' => $this->website->id,
                'url' => $this->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw so Laravel marks the job as failed
        }
    }
}
