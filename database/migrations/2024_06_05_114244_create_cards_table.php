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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('payment_system_id')->nullable();
            $table->timestamps();
            $table->string('card_pan', 32)->nullable();
            $table->string('card_type', 32)->nullable();
            $table->string('rectoken', 50)->nullable();
            $table->dateTime('rectoken_lifetime')->nullable();
            $table->boolean('default')->default(0);

            $table->foreign('account_id', 'cards_account_id_foreign')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('order_id', 'cards_order_id_foreign')->references('id')->on('orders');
            $table->foreign('payment_system_id', 'cards_payment_system_id_foreign')->references('id')->on('payment_systems');
            $table->foreign('user_id', 'cards_user_id_foreign')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
