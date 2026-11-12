<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lottery_types', function (Blueprint $table) {
            $table->unsignedInteger('bonus_coefficient')->default(10);
        });
    }

    public function down(): void
    {
        Schema::table('lottery_types', function (Blueprint $table) {
            $table->dropColumn('bonus_coefficient');
        });
    }
};
