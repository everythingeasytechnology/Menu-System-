<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly int $expiresInSeconds = 300,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your email verification OTP');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.auth.email-otp');
    }
}
