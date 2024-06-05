<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messenger_client', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('messengerable_id');
            $table->string('messengerable_type');
            $table->string('messenger_id');
            $table->string('token')->nullable();
            $table->integer('chat_id')->nullable();
            $table->unsignedBigInteger('current_project_id')->nullable();
            $table->text('message')->nullable();
            $table->integer('update_id')->nullable();
            $table->integer('message_id')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messenger_client');
    }
};
