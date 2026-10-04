<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_funds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->smallInteger('multiplier'); // 3–12
            $table->bigInteger('avg_monthly_expense');
            $table->bigInteger('target_amount');
            $table->bigInteger('saved_amount')->default(0);
            $table->smallInteger('plan_months');
            $table->bigInteger('monthly_amount');
            $table->enum('status', ['active', 'paused', 'completed'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_funds');
    }
};
