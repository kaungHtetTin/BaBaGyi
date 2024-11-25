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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('user_id'); 
            $table->integer('amount')->default(0);
            $table->boolean('verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->integer('bank_transaction_id');
            $table->timestamps();
            $table->index('user_id');
          
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions',function(Blueprint $table){
            $table->dropIndex(['user_id']);

            $table->dropForeign(['user_id']);
            $table->dropForeign(['payment_method_id']);
        });

        Schema::dropIfExists('transactions');
    }
};
