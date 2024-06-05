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
        Schema::create('plan_option', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('option_id');
            $table->string('value');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('option_id', 'plan_option_option_id_foreign')->references('id')->on('options')->onDelete('cascade');
            $table->foreign('plan_id', 'plan_option_plan_id_foreign')->references('id')->on('plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_option');
    }
};
