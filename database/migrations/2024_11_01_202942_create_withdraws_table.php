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
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('banking_id');
            $table->string('method')->nullable();
            $table->string('account_name')->nullable();
            $table->integer('amount')->default(0);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();

            $table->index('user_id');
          
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('banking_id')->references('id')->on('bankings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withdraws',function(Blueprint $table){
            $table->dropIndex(['user_id']);

            $table->dropForeign(['user_id']);
            $table->dropForeign(['banking_id']);
        });
        Schema::dropIfExists('withdraws');
    }
};
