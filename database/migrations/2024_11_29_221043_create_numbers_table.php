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
        Schema::create('numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clock_id');
            $table->unsignedBigInteger('lottery_type_id');
            $table->string('number');
            $table->integer('sell');
            $table->integer('demand');
            $table->boolean('disable')->default(false);
            $table->timestamps();

            $table->index('number');
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
         Schema::table('numbers',function(Blueprint $table){
            $table->dropIndex(['number']);
            $table->dropIndex(['clock_id']);

            $table->dropForeign(['clock_id']);
            $table->dropForeign(['lottery_type_id']);
        });

        Schema::dropIfExists('numbers');
    }
};
