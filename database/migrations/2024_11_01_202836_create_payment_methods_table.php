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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('banking_id');
            $table->string('method')->nullable();
            $table->string('account_name')->nullable();
            $table->integer('new_payment_count')->default(0)->nullable();
            $table->boolean('disable')->default(false)->nullable();
            $table->timestamps();

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
        Schema::table('sub_categories',function(Blueprint $table){
            $table->dropForeign(['banking_id']);
            
        });
        Schema::dropIfExists('payment_methods');
    }
};
