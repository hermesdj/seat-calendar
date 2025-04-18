<?php

use Illuminate\Database\Migrations\Migration;
use Seat\Services\Exceptions\SettingException;
use Seat\Services\Models\GlobalSetting;

class AddDefaultLocaleNotificationSettings extends Migration
{
    const DEFAULT_SETTINGS = [
        'kassie.calendar.notify_locale' => 'en',
    ];

    /**
     * Run the migrations.
     *
     * @throws SettingException
     */
    public function up(): void
    {
        foreach (self::DEFAULT_SETTINGS as $name => $value) {
            setting([$name, $value], true);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        GlobalSetting::whereIn('name', array_keys(self::DEFAULT_SETTINGS))->delete();
    }
}
