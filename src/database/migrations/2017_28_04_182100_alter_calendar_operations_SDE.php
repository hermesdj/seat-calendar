<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCalendarOperationsSDE extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('calendar_operations', function (Blueprint $table) {
                $table->dropForeign('calendar_operations_staging_sys_id_foreign');
            });
        } catch (QueryException|PDOException $e) {

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_operations', function (Blueprint $table) {
            $table->foreign('staging_sys_id')
                ->references('itemID')
                ->on('invUniqueNames')
                ->onDelete('set null');
        });
    }
}
