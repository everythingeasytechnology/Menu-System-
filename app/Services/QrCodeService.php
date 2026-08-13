<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Throwable;

class QrCodeService
{
    private const SCANNER_WIDTH = 640;

    private const SCANNER_HEIGHT = 760;

    private const QR_SIZE = 512;

    public function png(string $data): string
    {
        if (class_exists(Builder::class) && class_exists(PngWriter::class)) {
            return $this->pngWithEndroid($data);
        }

        throw new \RuntimeException('PNG QR generation is not available.');
    }

    public function scannerPng(string $data, string $businessName): string
    {
        $qrImage = imagecreatefromstring($this->png($data));

        if (! $qrImage) {
            throw new \RuntimeException('Unable to create QR scanner image.');
        }

        $canvas = imagecreatetruecolor(self::SCANNER_WIDTH, self::SCANNER_HEIGHT);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $ink = imagecolorallocate($canvas, 17, 24, 39);
        $muted = imagecolorallocate($canvas, 100, 116, 139);
        $orange = imagecolorallocate($canvas, 249, 115, 22);
        $lightOrange = imagecolorallocate($canvas, 255, 247, 237);
        $border = imagecolorallocate($canvas, 254, 215, 170);

        imagefilledrectangle($canvas, 0, 0, self::SCANNER_WIDTH, self::SCANNER_HEIGHT, $white);
        imagefilledrectangle($canvas, 32, 24, self::SCANNER_WIDTH - 32, 96, $lightOrange);
        imagerectangle($canvas, 32, 24, self::SCANNER_WIDTH - 32, 96, $border);

        $this->centerText($canvas, $this->shortText($businessName ?: 'EverythingEasy', 38), 5, 44, $ink);
        $this->centerText($canvas, 'Scan to Order', 3, 76, $orange);

        imagecopyresampled(
            $canvas,
            $qrImage,
            (int) ((self::SCANNER_WIDTH - self::QR_SIZE) / 2),
            126,
            0,
            0,
            self::QR_SIZE,
            self::QR_SIZE,
            imagesx($qrImage),
            imagesy($qrImage),
        );

        imagefilledrectangle($canvas, 56, 662, self::SCANNER_WIDTH - 56, 724, $lightOrange);
        imagerectangle($canvas, 56, 662, self::SCANNER_WIDTH - 56, 724, $border);
        $this->centerText($canvas, 'Powered by EverythingEasy Technology', 3, 686, $muted);

        ob_start();
        imagepng($canvas);
        $png = (string) ob_get_clean();

        imagedestroy($qrImage);
        imagedestroy($canvas);

        return $png;
    }

    public function svg(string $data): string
    {
        if (class_exists(Builder::class)) {
            try {
                return $this->svgWithEndroid($data);
            } catch (Throwable) {
                // Fall through to Bacon QR if the installed Endroid version is incomplete.
            }
        }

        if (class_exists(Writer::class)) {
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
        $result = (new Builder(
            writer: new SvgWriter,
            writerOptions: [
                SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => false,
            ],
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 512,
            margin: 24,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        return $result->getString();
    }

    private function pngWithEndroid(string $data): string
    {
        $result = (new Builder(
            writer: new PngWriter,
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 512,
            margin: 24,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        return $result->getString();
    }

    private function centerText(\GdImage $image, string $text, int $font, int $y, int $color): void
    {
        $x = (int) ((imagesx($image) - imagefontwidth($font) * strlen($text)) / 2);

        imagestring($image, $font, max(0, $x), $y, $text, $color);
    }

    private function shortText(string $text, int $maxLength): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?: '');

        if (strlen($text) <= $maxLength) {
            return $text;
        }

        return rtrim(substr($text, 0, $maxLength - 3)).'...';
    }

    private function svgWithBacon(string $data): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(512, 3),
            new SvgImageBackEnd,
        );

        $writer = new Writer($renderer);

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
