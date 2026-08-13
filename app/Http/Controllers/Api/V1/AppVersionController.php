<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppVersionController extends ApiController
{
    public function show(): JsonResponse
    {
        return $this->success($this->payload(AppVersion::current()), 'App version');
    }

    public function update(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'superadmin') {
            return $this->error('Only superadmin can update app version.', 403);
        }

        $data = $request->validate([
            'version' => ['required', 'string', 'max:50'],
        ]);

        $appVersion = AppVersion::current();
        $appVersion->update(['version' => $data['version']]);

        return $this->success($this->payload($appVersion->fresh()), 'App version updated');
    }

    private function payload(AppVersion $appVersion): array
    {
        return [
            'version' => $appVersion->version,
            'updated_at' => $appVersion->updated_at?->toIso8601String(),
        ];
    }
}
