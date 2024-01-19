<?php

use Illuminate\Database\Migrations\Migration;
use Seat\Services\Exceptions\SettingException;
use Seat\Services\Models\GlobalSetting;

class DefaultNotificationSettings extends Migration
{
    const DEFAULT_SETTINGS = [
        'kassie.calendar.notify_create_operation' => true,
        'kassie.calendar.notify_update_operation' => true,
        'kassie.calendar.notify_cancel_operation' => true,
        'kassie.calendar.notify_activate_operation' => true,
        'kassie.calendar.notify_end_operation' => true,
        'kassie.calendar.notify_operation_interval' => '15,30,60',
    ];

    /**
     * Run the migrations.
     *
     * @return void
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
     *
     * @return void
     */
    public function down(): void
    {
        GlobalSetting::whereIn('name', array_keys(self::DEFAULT_SETTINGS))->delete();
    }
}