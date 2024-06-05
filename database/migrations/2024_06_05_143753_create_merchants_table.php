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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_system_id');
            $table->string('secret_key',1024);
            $table->string('merchant_id');
            $table->string('api_version', 10)->nullable();
            $table->enum('status', ['active', 'not_active']);
            $table->timestamps();

            $table->foreign('payment_system_id', 'merchants_payment_system_id_foreign')->references('id')->on('payment_systems')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
