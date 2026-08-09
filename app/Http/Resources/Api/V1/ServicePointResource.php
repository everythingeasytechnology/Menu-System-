<?php

namespace App\Http\Resources\Api\V1;

use App\Services\ScanUrlService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicePointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'qr_identifier' => $this->qr_identifier,
            'name' => $this->name,
            'seats' => $this->seats,
            'category' => $this->category,
            'point_type' => $this->point_type,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'scan_url' => ScanUrlService::forQr($this->qr_identifier),
            'scanner_download_url' => url('/api/v1/service-points/'.$this->id.'/scanner'),
            'order_number' => $this->order_number,
            'amount' => (float) $this->amount,
            'items' => $this->items ?? [],
        ];
    }
}
