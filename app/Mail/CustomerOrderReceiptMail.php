<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly array $payload,
        public readonly bool $isUpdate = false,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isUpdate
                ? 'Your bill is updated for '.$this->order->compactNumber()
                : 'Your order '.$this->order->compactNumber().' details and bill',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.orders.customer-order-receipt');
    }
}
