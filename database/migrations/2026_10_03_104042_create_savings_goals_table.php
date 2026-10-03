<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->bigInteger('price');
            $table->bigInteger('saved_amount')->default(0);
            $table->date('target_date');
            $table->bigInteger('monthly_amount');
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->timestampTz('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};
