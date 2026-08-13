<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;

class AppVersionController extends ApiController
{
    public function show(): JsonResponse
    {
        return $this->success($this->payload(AppVersion::current()), 'App version');
    }

    private function payload(AppVersion $appVersion): array
    {
        return [
            'version' => $appVersion->version,
            'updated_at' => $appVersion->updated_at?->toIso8601String(),
        ];
    }
}
