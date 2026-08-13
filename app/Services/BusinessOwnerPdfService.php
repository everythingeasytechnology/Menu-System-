<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Str;

class BusinessOwnerPdfService
{
    private const PAGE_WIDTH = 595;

    private const PAGE_HEIGHT = 842;

    private const MARGIN = 42;

    private const BOTTOM = 52;

    private array $pages = [];

    private array $lines = [];

    private int $y = 790;

    public function make(Business $business): string
    {
        $this->pages = [];
        $this->lines = [];
        $this->y = 790;

        $business->loadMissing(['owner', 'businessSetting']);
        $business->loadCount(['orders', 'menuItems', 'categories', 'restaurantTables', 'rooms', 'servicePoints']);

        $settings = $business->businessSetting;
        $owner = $business->owner;

        $this->text('Business Owner Details', 22, true);
        $this->text('Generated: '.now()->format('d M Y, h:i A'), 9);
        $this->gap(12);

        $this->section('Business');
        $this->row('Business Name', $business->name);
        $this->row('Type', $business->type);
        $this->row('Status', ucfirst((string) $business->status));
        $this->row('Business Email', $business->email);
        $this->row('Business Phone', $business->phone);
        $this->row('Address', $business->address);
        $this->row('City / State / Country', collect([$business->city, $business->state, $business->country])->filter()->join(', '));
        $this->row('Timezone', $business->timezone);
        $this->row('Created At', $business->created_at?->format('d M Y, h:i A'));

        $this->section('Owner');
        $this->row('Owner Name', $owner?->name);
        $this->row('Owner Email', $owner?->email);
        $this->row('Owner Phone', $owner?->phone);
        $this->row('Owner Status', $owner?->status ? ucfirst($owner->status) : null);
        $this->row('Owner Role', $owner?->role);

        $this->section('Business Settings');
        $this->row('Brand Name', $settings?->brand_name);
        $this->row('Settings Email', $settings?->business_email);
        $this->row('Shop No', $settings?->shop_no);
        $this->row('Address', $settings?->address);
        $this->row('District / State / Country', collect([$settings?->district, $settings?->state, $settings?->country])->filter()->join(', '));
        $this->row('Pincode', $settings?->pincode);
        $this->row('GST Enabled', $settings?->gst_enabled ? 'Yes' : 'No');
        $this->row('GST No', $settings?->gst_no);
        $this->row('CGST / SGST', trim(($settings?->cgst ?? '0').' / '.($settings?->sgst ?? '0')));
        $this->row('Latitude / Longitude', collect([$settings?->latitude, $settings?->longitude])->filter()->join(' / '));
        $this->row('Logo Path', $settings?->logo_path ?: $business->logo_path);

        $this->section('Platform Counts');
        $this->row('Orders', (string) $business->orders_count);
        $this->row('Menu Categories', (string) $business->categories_count);
        $this->row('Menu Items', (string) $business->menu_items_count);
        $this->row('Tables', (string) $business->restaurant_tables_count);
        $this->row('Rooms', (string) $business->rooms_count);
        $this->row('Service Points', (string) $business->service_points_count);

        $this->finishPage();

        return $this->buildPdf();
    }

    private function section(string $title): void
    {
        $this->gap(8);
        $this->text($title, 13, true);
        $this->line();
    }

    private function row(string $label, mixed $value): void
    {
        $value = filled($value) ? (string) $value : 'Not set';
        $wrapped = explode("\n", wordwrap($this->normalize($value), 68, "\n", true));
        $first = true;

        foreach ($wrapped as $line) {
            $prefix = $first ? $label.': ' : str_repeat(' ', min(28, strlen($label) + 2));
            $this->text($prefix.$line, 10);
            $first = false;
        }
    }

    private function text(string $text, int $size = 10, bool $bold = false): void
    {
        $lineHeight = $size + 5;

        if ($this->y - $lineHeight < self::BOTTOM) {
            $this->finishPage();
        }

        $font = $bold ? 'F2' : 'F1';
        $this->lines[] = sprintf('BT /%s %d Tf %.2F %.2F Td (%s) Tj ET', $font, $size, self::MARGIN, $this->y, $this->escape($this->normalize($text)));
        $this->y -= $lineHeight;
    }

    private function line(): void
    {
        if ($this->y - 8 < self::BOTTOM) {
            $this->finishPage();
        }

        $this->lines[] = sprintf('%.2F %.2F m %.2F %.2F l S', self::MARGIN, $this->y, self::PAGE_WIDTH - self::MARGIN, $this->y);
        $this->y -= 10;
    }

    private function gap(int $height): void
    {
        $this->y -= $height;
    }

    private function finishPage(): void
    {
        if ($this->lines === []) {
            return;
        }

        $this->pages[] = implode("\n", [
            '0.96 0.96 0.96 rg',
            sprintf('0 0 %.2F %.2F re f', self::PAGE_WIDTH, self::PAGE_HEIGHT),
            '0 0 0 RG',
            '0 0 0 rg',
            ...$this->lines,
        ]);

        $this->lines = [];
        $this->y = 790;
    }

    private function buildPdf(): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];

        $kids = [];
        $nextObject = 5;

        foreach ($this->pages as $pageContent) {
            $contentObject = $nextObject++;
            $pageObject = $nextObject++;
            $kids[] = $pageObject.' 0 R';
            $stream = $pageContent."\n";

            $objects[$contentObject] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream';
            $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::PAGE_WIDTH.' '.self::PAGE_HEIGHT.'] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents '.$contentObject.' 0 R >>';
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (range(1, count($objects)) as $number) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    private function normalize(string $value): string
    {
        return Str::ascii(trim(preg_replace('/\s+/', ' ', $value)));
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $value);
    }
}
