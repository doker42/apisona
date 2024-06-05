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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->nullable();
            $table->unsignedBigInteger('plan_range_id')->nullable();
            $table->string('status')->nullable();
            $table->string('zoho_deal_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('plan_range_id', 'orders_plan_range_id_foreign')->references('id')->on('plan_ranges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
