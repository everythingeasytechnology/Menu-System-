<?php

namespace App\Services;

use App\Models\MailSetting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MailBrandingService
{
    public const DEFAULT_BRAND_NAME = 'EverythingEasy';

    public function apply(): void
    {
        config([
            'app.name' => $this->name(),
        ]);
    }

    public function name(): string
    {
        $name = $this->mailFromName();

        if ($name && ! str($name)->lower()->contains('laravel')) {
            return $name;
        }

        return self::DEFAULT_BRAND_NAME;
    }

    public function logoUrl(): ?string
    {
        $path = $this->superAdminLogoPath();

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    private function mailFromName(): ?string
    {
        try {
            if (Schema::hasTable('mail_settings')) {
                $name = MailSetting::query()->value('from_name');

                if (filled($name)) {
                    return $name;
                }
            }
        } catch (Throwable) {
            //
        }

        return config('mail.from.name');
    }

    private function superAdminLogoPath(): ?string
    {
        try {
            if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'profile_image_path')) {
                return null;
            }

            return User::query()
                ->where('role', 'superadmin')
                ->whereNotNull('profile_image_path')
                ->where('profile_image_path', '!=', '')
                ->orderByDesc('status')
                ->orderBy('id')
                ->value('profile_image_path');
        } catch (Throwable) {
            return null;
        }
    }
}
