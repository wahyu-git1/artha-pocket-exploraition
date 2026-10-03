<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->char('month', 7); // YYYY-MM
            $table->uuid('income_id')->nullable(); // NULL = berlaku untuk semua pendapatan
            $table->foreign('income_id')->references('id')->on('incomes')->restrictOnDelete();
            $table->string('template_code', 20)->nullable(); // e.g. 50-30-20
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'month', 'income_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_plans');
    }
};
