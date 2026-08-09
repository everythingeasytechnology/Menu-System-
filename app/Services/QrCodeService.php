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

        return $this->fallbackSvg($data);
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

    private function fallbackSvg(string $data): string
    {
        $escaped = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">
  <rect width="512" height="512" fill="#ffffff"/>
  <rect x="32" y="32" width="448" height="448" rx="24" fill="#fff7ed" stroke="#fb923c" stroke-width="6"/>
  <text x="256" y="180" text-anchor="middle" font-family="Arial, sans-serif" font-size="28" font-weight="700" fill="#1f2937">Scanner unavailable</text>
  <text x="256" y="225" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="#64748b">Open this scan link manually:</text>
  <foreignObject x="66" y="250" width="380" height="140">
    <div xmlns="http://www.w3.org/1999/xhtml" style="font-family:Arial,sans-serif;font-size:16px;line-height:1.35;color:#0f172a;word-break:break-all;text-align:center;">{$escaped}</div>
  </foreignObject>
</svg>
SVG;
    }
}
