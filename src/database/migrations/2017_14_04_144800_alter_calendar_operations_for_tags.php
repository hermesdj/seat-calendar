<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCalendarOperationsForTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('calendar_operations', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::create('calendar_tags', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->string('bg_color');
            $table->string('text_color');
        });

        Schema::create('calendar_tag_operation', function (Blueprint $table) {
            $table->integer('tag_id')->unsigned()->nullable();
            $table->integer('operation_id')->unsigned()->nullable();

            $table->foreign('tag_id')
                ->references('id')
                ->on('calendar_tags')
                ->onDelete('cascade');

            $table->foreign('operation_id')
                ->references('id')
                ->on('calendar_operations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('calendar_operations', function (Blueprint $table) {
            $table->enum('type', ['PvP', 'PvE', 'PvR', 'Other'])->nullable();
        });

        Schema::table('calendar_tag_operation', function (Blueprint $table) {
            $table->dropForeign('calendar_tag_operation_operation_id_foreign');
            $table->dropForeign('calendar_tag_operation_tag_id_foreign');
        });

        Schema::drop('calendar_tag_operation');
        Schema::drop('calendar_tags');
    }
}
