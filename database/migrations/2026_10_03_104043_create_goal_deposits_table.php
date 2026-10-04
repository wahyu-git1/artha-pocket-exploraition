<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goal_deposits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('goal_id');
            $table->foreign('goal_id')->references('id')->on('savings_goals')->cascadeOnDelete();
            $table->uuid('income_id');
            $table->foreign('income_id')->references('id')->on('incomes')->restrictOnDelete();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->bigInteger('amount'); // > 0
            $table->date('deposited_at');
            $table->string('note', 255)->nullable();
            $table->uuid('client_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'client_id']);
            $table->index(['goal_id', 'deposited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_deposits');
    }
};
