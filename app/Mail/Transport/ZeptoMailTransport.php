<?php

namespace App\Mail\Transport;

use Illuminate\Http\Client\Factory as HttpFactory;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class ZeptoMailTransport extends AbstractTransport
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $host,
        private readonly string $apiKey,
        private readonly int $timeout = 30,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new RuntimeException('ZeptoMail can only send Symfony Email messages.');
        }

        $payload = [
            'from' => $this->emailAddress($email->getFrom()[0]),
            'to' => $this->recipients($email->getTo()),
            'subject' => $email->getSubject() ?? '',
        ];

        if ($email->getHtmlBody() !== null) {
            $payload['htmlbody'] = $this->bodyToString($email->getHtmlBody());
        }

        if ($email->getTextBody() !== null) {
            $payload['textbody'] = $this->bodyToString($email->getTextBody());
        }

        if ($email->getCc() !== []) {
            $payload['cc'] = $this->recipients($email->getCc());
        }

        if ($email->getBcc() !== []) {
            $payload['bcc'] = $this->recipients($email->getBcc());
        }

        if ($email->getReplyTo() !== []) {
            $payload['reply_to'] = $this->emailAddress($email->getReplyTo()[0]);
        }

        $attachments = array_map(
            fn (DataPart $attachment): array => [
                'content' => base64_encode($this->bodyToString($attachment->getBody())),
                'name' => $attachment->getFilename() ?? 'attachment',
                'mime_type' => $attachment->getMediaType().'/'.$attachment->getMediaSubtype(),
            ],
            $email->getAttachments(),
        );

        if ($attachments !== []) {
            $payload['attachments'] = $attachments;
        }

        $response = $this->http
            ->withHeaders([
                'Authorization' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->asJson()
            ->timeout($this->timeout)
            ->post($this->endpoint(), $payload);

        if ($response->failed()) {
            throw new RuntimeException(sprintf(
                'ZeptoMail request failed with HTTP %d: %s',
                $response->status(),
                $response->body(),
            ));
        }

        $requestId = $response->json('request_id') ?? $response->json('data.0.request_id');

        if (is_string($requestId) && $requestId !== '') {
            $message->setMessageId($requestId);
        }
    }

    public function __toString(): string
    {
        return 'zeptomail+api://'.$this->host;
    }

    private function endpoint(): string
    {
        return 'https://'.rtrim(preg_replace('#^https?://#', '', $this->host), '/').'/api/v2/email';
    }

    /** @param Address[] $addresses */
    private function recipients(array $addresses): array
    {
        return array_map(
            fn (Address $address): array => ['email_address' => $this->emailAddress($address)],
            $addresses,
        );
    }

    private function emailAddress(Address $address): array
    {
        return array_filter([
            'address' => $address->getAddress(),
            'name' => $address->getName(),
        ], fn (string $value): bool => $value !== '');
    }

    /** @param resource|string $body */
    private function bodyToString($body): string
    {
        if (is_resource($body)) {
            rewind($body);

            return stream_get_contents($body) ?: '';
        }

        return (string) $body;
    }
}
