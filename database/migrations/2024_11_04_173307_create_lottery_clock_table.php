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
        Schema::create('lottery_clock', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lottery_type_id');
            $table->unsignedBigInteger('clock_id');
            $table->integer('close_before')->default(30);
            $table->timestamps();

            $table->foreign('lottery_type_id')->references('id')->on('lottery_types')->onDelete('cascade');
            $table->foreign('clock_id')->references('id')->on('clocks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lottery_clock',function(Blueprint $table){

            $table->dropForeign(['clock_id']);
            $table->dropForeign(['lottery_type_id']);
        });
        Schema::dropIfExists('lottery_clock');
    }
};
