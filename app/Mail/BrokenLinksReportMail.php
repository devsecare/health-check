<?php

namespace App\Mail;

use App\Models\BrokenLink;
use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BrokenLinksReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public BrokenLink $check,
        public Website $website
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $brokenCount = $this->check->total_broken ?? 0;
        $subject = $brokenCount > 0 
            ? "Broken Links Found: {$brokenCount} issues on {$this->website->name}"
            : "No Broken Links Found on {$this->website->name}";
            
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
            view: 'emails.broken-links-report',
            with: [
                'check' => $this->check,
                'website' => $this->website,
                'summary' => $this->check->summary ?? [],
                'brokenLinks' => array_slice($this->check->broken_links_data ?? [], 0, 50), // First 50 for email
                'totalChecked' => $this->check->total_checked ?? 0,
                'totalBroken' => $this->check->total_broken ?? 0,
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
