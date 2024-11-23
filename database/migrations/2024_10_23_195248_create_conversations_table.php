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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');     
            $table->unsignedBigInteger('my_id');  
            $table->unsignedBigInteger('sender_id');  
            $table->string('message')->nullable();
            $table->integer('new_message_count')->default(0);
            $table->boolean('seen')->default(false)->nullable();

            $table->index('my_id');
            $table->foreign('my_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations',function(Blueprint $table){
            $table->dropIndex(['my_id']);
            $table->dropForeign(['my_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('conversations');
    }
};
