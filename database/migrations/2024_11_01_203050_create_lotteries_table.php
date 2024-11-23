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
        Schema::create('lotteries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lottery_type_id');
            $table->unsignedBigInteger('clock_id');
            $table->string('number');
            $table->timestamps();

            $table->index('lottery_type_id');
            $table->index('clock_id');

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
        Schema::table('lotteries',function(Blueprint $table){
            $table->dropIndex(['lottery_type_id']);
            $table->dropIndex(['clock_id']);

            $table->dropForeign(['lottery_type_id']);
            $table->dropForeign(['clock_id']);
        });
        Schema::dropIfExists('lotteries');
    }
};
