<?php

namespace App\Services;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MailSettingsService
{
    public function current(): ?MailSetting
    {
        if (! $this->tableExists()) {
            return null;
        }

        return MailSetting::query()->first();
    }

    public function apply(?MailSetting $setting = null): bool
    {
        $setting ??= $this->current();

        if (! $setting || ! $setting->enabled) {
            return false;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $setting->host,
            'mail.mailers.smtp.port' => $setting->port,
            'mail.mailers.smtp.encryption' => $setting->encryption ?: null,
            'mail.mailers.smtp.username' => $setting->username ?: null,
            'mail.mailers.smtp.password' => $setting->password ?: null,
            'mail.mailers.smtp.timeout' => $setting->timeout ?: 30,
            'mail.from.address' => $setting->from_address,
            'mail.from.name' => $setting->from_name ?: config('app.name'),
        ]);

        if (app()->bound('mail.manager')) {
            app('mail.manager')->purge('smtp');
        }

        return true;
    }

    public function enabled(): bool
    {
        return (bool) $this->current()?->enabled;
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('mail_settings');
        } catch (Throwable) {
            return false;
        }
    }
}
