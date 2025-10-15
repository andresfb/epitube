<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EncodeErrorsMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $pendingCount) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Encoding Errors',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.encode-errors',
            with: [
                'url' => route('encoding.errors'),
                'count' => $this->pendingCount,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
