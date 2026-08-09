<?php

namespace App\Services;

use Throwable;

class QrCodeService
{
    public function svg(string $data): string
    {
        if (class_exists(\Endroid\QrCode\Builder\Builder::class)) {
            try {
                return $this->svgWithEndroid($data);
            } catch (Throwable) {
                // Fall through to Bacon QR if the installed Endroid version is incomplete.
            }
        }

        if (class_exists(\BaconQrCode\Writer::class)) {
            try {
                return $this->svgWithBacon($data);
            } catch (Throwable) {
                // Return a non-crashing SVG below so scanner download never 500s.
            }
        }

        return $this->remoteQrSvg($data);
    }

    private function svgWithEndroid(string $data): string
    {
        $result = (new \Endroid\QrCode\Builder\Builder(
            writer: new \Endroid\QrCode\Writer\SvgWriter(),
            writerOptions: [
                \Endroid\QrCode\Writer\SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => false,
            ],
            data: $data,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
            size: 512,
            margin: 24,
            roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
        ))->build();

        return $result->getString();
    }

    private function svgWithBacon(string $data): string
    {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(512, 3),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd(),
        );

        $writer = new \BaconQrCode\Writer($renderer);

        return $writer->writeString($data, 'UTF-8', \BaconQrCode\Common\ErrorCorrectionLevel::H());
    }

    private function remoteQrSvg(string $data): string
    {
        $escaped = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        $qrImageUrl = htmlspecialchars(
            'https://api.qrserver.com/v1/create-qr-code/?size=512x512&margin=24&data='.rawurlencode($data),
            ENT_QUOTES,
            'UTF-8'
        );

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="640" viewBox="0 0 512 640">
  <rect width="512" height="512" fill="#ffffff"/>
  <image x="0" y="0" width="512" height="512" href="{$qrImageUrl}" xlink:href="{$qrImageUrl}"/>
  <rect x="20" y="532" width="472" height="88" rx="16" fill="#fff7ed" stroke="#fed7aa" stroke-width="2"/>
  <text x="256" y="568" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" font-weight="700" fill="#f97316">Scan to Order</text>
  <foreignObject x="46" y="582" width="420" height="30">
    <div xmlns="http://www.w3.org/1999/xhtml" style="font-family:Arial,sans-serif;font-size:12px;line-height:1.25;color:#475569;word-break:break-all;text-align:center;">{$escaped}</div>
  </foreignObject>
</svg>
SVG;
    }
}
