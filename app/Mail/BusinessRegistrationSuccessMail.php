<?php

namespace App\Mail;

use App\Models\Business;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BusinessRegistrationSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Business $business,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your business registration is successful');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.auth.business-registration-success');
    }
}
