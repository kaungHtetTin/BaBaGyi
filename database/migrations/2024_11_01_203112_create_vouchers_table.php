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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lottery_type_id');
            $table->unsignedBigInteger('clock_id');
            $table->string('number');
            $table->integer('amount')->default(0);
            $table->boolean('win')->default(0);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->boolean('verified')->default(0);
            
            $table->timestamps();

            $table->index('user_id');
            $table->index('number');
            $table->index('win');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::table('vouchers',function(Blueprint $table){
            $table->dropIndex(['user_id']);
            $table->dropIndex(['number']);
            $table->dropIndex(['win']);

            $table->dropForeign(['user_id']);
            $table->dropForeign(['clock_id']);
            $table->dropForeign(['lottery_type_id']);
        });
        Schema::dropIfExists('vouchers');
    }
};
