<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Seat\Services\Exceptions\SettingException;

class UseCoreIntegrations extends Migration
{
    /**
     * @throws SettingException
     */
    public function up(): void
    {

        $integration = setting('kassie.calendar.slack_webhook', true);

        if (! is_null($integration)) {
            DB::table('integrations')->insert([
                'name' => 'SeAT Calendar',
                'type' => 'slack',
                'settings' => json_encode((object) ['url' => $integration]),
            ]);

            DB::table('global_settings')->where('name', 'kassie.calendar.slack_webhook')->delete();
        }

        Schema::table('calendar_operations', function (Blueprint $table) {

            $table->unsignedInteger('integration_id')->nullable();

        });
    }

    /**
     * @throws SettingException
     */
    public function down(): void
    {
        $integration = DB::table('integrations')->where('name', 'SeAT Calendar')->where('type', 'slack')->first();

        if (! is_null($integration)) {
            setting(['kassie.calendar.slack_webhook' => json_decode($integration->settings)->url], true);
        }

        Schema::table('calendar_operations', function (Blueprint $table) {

            $table->dropColumn('integration_id');

        });
    }
}
