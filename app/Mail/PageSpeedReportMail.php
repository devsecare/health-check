<?php

namespace App\Mail;

use App\Models\PageSpeedInsight;
use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PageSpeedReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public PageSpeedInsight $insight,
        public Website $website
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $score = $this->insight->performance_score ?? 0;
        $strategy = ucfirst($this->insight->strategy ?? 'mobile');
        $subject = "PageSpeed Insights Report ({$strategy}): {$score}/100 - {$this->website->name}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pagespeed-report',
            with: [
                'insight' => $this->insight,
                'website' => $this->website,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
