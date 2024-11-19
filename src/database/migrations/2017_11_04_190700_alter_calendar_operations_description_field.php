<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCalendarOperationsDescriptionField extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('calendar_operations', function (Blueprint $table) {
            $table->text('description_new')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_operations', function (Blueprint $table) {
            $table->dropColumn('description_new');
        });
    }
}
