<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('request_id');
            $table->string('fingerprint', 64);
            $table->unsignedSmallInteger('status');
            $table->longText('body');
            $table->timestamp('created_at');
            $table->unique(['user_id', 'request_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_requests');
    }
};
