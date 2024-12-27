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
        Schema::create('hot_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lottery_type_id');
            $table->unsignedBigInteger('clock_id');
            $table->string('number');
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
        Schema::table('hot_numbers',function(Blueprint $table){
            $table->dropForeign(['clock_id']);
            $table->dropForeign(['lottery_type_id']);
        });
        Schema::dropIfExists('hot_numbers');
    }
};
