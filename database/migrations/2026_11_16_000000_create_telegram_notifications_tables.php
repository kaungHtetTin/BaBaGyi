<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('telegram_settings', function (Blueprint $table) {
            $table->id();
            $table->text('bot_token')->nullable();
            $table->string('admin_chat_id')->nullable();
            $table->boolean('enabled')->default(false);
            $table->boolean('notify_topups')->default(true);
            $table->boolean('notify_withdrawals')->default(true);
            $table->timestamps();
        });
        Schema::create('telegram_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->unsignedBigInteger('request_id');
            $table->text('message');
            $table->string('chat_id');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->string('last_error')->nullable();
            $table->timestamps();
            $table->unique(['type', 'request_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('telegram_alerts');
        Schema::dropIfExists('telegram_settings');
    }
};
