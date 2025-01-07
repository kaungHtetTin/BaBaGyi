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
        Schema::create('lottery_types', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('coefficient')->default(1);
            $table->integer('close_before')->default(30);
            $table->boolean('release_mode')->default(true);
            $table->boolean('open')->default(true);
            $table->text('api_url')->nullable();
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
        Schema::dropIfExists('lottery_types');
    }
};
