<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFleetTable extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_pap_fleets', function (Blueprint $table) {
            $table->integer('operation_id')->unsigned();
            $table->bigInteger('fleet_commander_id');
            $table->bigInteger('fleet_id');

            $table->foreign('operation_id')
                ->references('id')
                ->on('calendar_operations')
                ->onDelete('cascade');

            $table->foreign('fleet_commander_id')
                ->references('character_id')
                ->on('character_infos')
                ->onDelete('cascade');

            $table->primary(['operation_id', 'fleet_commander_id']);
        });
    }

    public function down(): void
    {
        Schema::table('calendar_pap_fleets', function (Blueprint $table) {
            $table->dropForeign('calendar_pap_fleets_operation_id_foreign');
            $table->dropForeign('calendar_pap_fleets_fleet_commander_id_foreign');
        });

        Schema::drop('calendar_pap_fleets');
    }
}
