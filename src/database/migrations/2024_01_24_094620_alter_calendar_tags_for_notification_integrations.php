<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCalendarTagsForNotificationIntegrations extends Migration
{

    public function up(): void
    {
        Schema::create('calendar_tag_integration', function (Blueprint $table) {
            $table->integer('tag_id')->unsigned();
            $table->integer('integration_id')->unsigned();

            $table->foreign('tag_id')
                ->references('id')
                ->on('calendar_tags')
                ->onDelete('cascade');

            $table->foreign('integration_id')
                ->references('id')
                ->on('integrations')
                ->onDelete('cascade');

            $table->primary(['tag_id', 'integration_id']);
        });
    }

    public function down(): void
    {
        Schema::table('calendar_tag_integration', function (Blueprint $table) {
            $table->dropForeign('calendar_tag_integration_integration_id_foreign');
            $table->dropForeign('calendar_tag_integration_tag_id_foreign');
        });

        Schema::drop('calendar_tag_integration');
    }
}
