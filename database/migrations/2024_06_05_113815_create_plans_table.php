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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id');
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('icon', '255');
            $table->string('description', 1024)->nullable();
            $table->boolean('available')->default(1);
            $table->boolean('custom')->default(false);
            $table->unsignedBigInteger('account_id')->nullable();
            $table->timestamps();

            $table->foreign('currency_id', 'plans_currency_id_foreign')->references('id')->on('currencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
