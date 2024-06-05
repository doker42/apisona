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
        Schema::create('plan_ranges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('currency_id');
            $table->integer('weight');
            $table->enum('regular_mode', ['yearly', 'monthly', 'quarterly'])->default('monthly');
            $table->double('amount', 10, 2)->default(0.00);
            $table->double('discount', 10, 2)->default(0.00);
            $table->boolean('trial')->default(false);

            $table->string('crm_product_id')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('currency_id', 'plan_ranges_currency_id_foreign')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('plan_id', 'plan_ranges_plan_id_foreign')->references('id')->on('plans')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('plan_ranges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_ranges');
    }
};
