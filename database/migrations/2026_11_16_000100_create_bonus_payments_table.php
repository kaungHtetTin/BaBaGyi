<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bonus_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('wallet_history_id')->unique()->constrained('wallet_histories')->cascadeOnDelete();
            $table->uuid('request_id');
            $table->unsignedInteger('amount');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'request_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonus_payments');
    }
};
