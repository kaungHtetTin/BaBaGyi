<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clock_id');
            $table->unsignedBigInteger('lottery_type_id');
            $table->timestamps();

            $table->index('lottery_type_id');
            $table->index('clock_id');
            $table->foreign('clock_id')->references('id')->on('clocks')->onDelete('cascade');
            $table->foreign('lottery_type_id')->references('id')->on('lottery_types')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports',function(Blueprint $table){
            $table->dropIndex(['lottery_type_id']);
            $table->dropIndex(['clock_id']);

            $table->dropForeign(['clock_id']);
            $table->dropForeign(['lottery_type_id']);
        });
        Schema::dropIfExists('reports');

    }
};
