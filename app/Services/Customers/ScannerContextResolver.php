<?php

namespace App\Services\Customers;

use App\Models\Business;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ServicePoint;
use Illuminate\Validation\ValidationException;

class ScannerContextResolver
{
    public function resolve(string $qr): array
    {
        $table = RestaurantTable::with('business.businessSetting')
            ->where('qr_identifier', $qr)
            ->where('is_active', true)
            ->first();

        if ($this->hasActiveBusiness($table?->business)) {
            return [$table->business, [
                'type' => 'table',
                'id' => $table->id,
                'name' => $table->name,
                'qr_identifier' => $table->qr_identifier,
            ]];
        }

        $room = Room::with('business.businessSetting')
            ->where('qr_identifier', $qr)
            ->where('is_active', true)
            ->first();

        if ($this->hasActiveBusiness($room?->business)) {
            return [$room->business, [
                'type' => 'room',
                'id' => $room->id,
                'name' => $room->name,
                'qr_identifier' => $room->qr_identifier,
            ]];
        }

        $servicePoint = ServicePoint::with('business.businessSetting')
            ->where(function ($query) use ($qr) {
                $query->where('qr_identifier', $qr)
                    ->orWhere('code', $qr);
            })
            ->where('is_active', true)
            ->first();

        if ($this->hasActiveBusiness($servicePoint?->business)) {
            return [$servicePoint->business, [
                'type' => 'service_point',
                'id' => $servicePoint->id,
                'name' => $servicePoint->name,
                'category' => $servicePoint->category,
                'point_type' => $servicePoint->point_type,
                'qr_identifier' => $servicePoint->qr_identifier,
            ]];
        }

        throw ValidationException::withMessages(['qr' => ['QR identifier is invalid or inactive.']]);
    }

    private function hasActiveBusiness(?Business $business): bool
    {
        return $business !== null && $business->status === 'active';
    }
}
