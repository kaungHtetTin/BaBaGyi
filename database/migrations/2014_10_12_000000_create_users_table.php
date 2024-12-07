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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('recovery_hint');
            $table->integer('balance')->default(0);
            $table->string('fcm_token')->nullable();
            $table->string('avatar_url')->nullable();
            $table->integer('login_time')->default(1);
            $table->boolean('admin')->default(false)->nullable();
            $table->boolean('disable')->default(false)->nullable();
            $table->timestamps();

            $table->index('phone');
        
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users',function(Blueprint $table){
            $table->dropIndex(['phone']);
        });
        Schema::dropIfExists('users');
    }
};
