<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOperationTable extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_operations', function (Blueprint $table) {
            $table->unsignedInteger('doctrine_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('calendar_operations', function (Blueprint $table) {
            $table->dropColumn('doctrine_id');
        });
    }
}
