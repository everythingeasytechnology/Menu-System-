<?php

namespace App\Services;

class ScanUrlService
{
    public static function forQr(string $qrIdentifier): string
    {
        $customerMenuUrl = trim((string) config('app.customer_menu_url', ''));

        if ($customerMenuUrl === '') {
            return url('/api/v1/public/menu/'.$qrIdentifier);
        }

        $encodedQr = rawurlencode($qrIdentifier);
        foreach (['{qr}', '{qr_identifier}', '{point}'] as $placeholder) {
            if (str_contains($customerMenuUrl, $placeholder)) {
                return str_replace($placeholder, $encodedQr, $customerMenuUrl);
            }
        }

        $separator = str_contains($customerMenuUrl, '?') ? '&' : '?';

        return rtrim($customerMenuUrl, '?&').$separator.'qr='.$encodedQr;
    }
}
