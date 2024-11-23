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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('my_id');
            $table->text('message')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('seen')->default(false)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('my_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('my_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages',function(Blueprint $table){
            $table->dropIndex(['my_id']);
            $table->dropIndex(['user_id']);

            $table->dropForeign(['my_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('messages');
    }
};
