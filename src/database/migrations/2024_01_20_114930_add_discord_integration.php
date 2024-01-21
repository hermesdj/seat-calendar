<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscordIntegration extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_operations', function (Blueprint $table) {
            $table->string('discord_guild_event_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('calendar_operations', function (Blueprint $table) {
            $table->dropColumn('discord_guild_event_id');
        });
    }
}