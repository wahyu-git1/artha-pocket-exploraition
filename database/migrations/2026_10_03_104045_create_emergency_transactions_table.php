<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fund_id');
            $table->foreign('fund_id')->references('id')->on('emergency_funds')->cascadeOnDelete();
            $table->uuid('income_id')->nullable();
            $table->foreign('income_id')->references('id')->on('incomes')->restrictOnDelete();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->bigInteger('amount'); // > 0
            $table->string('reason', 255)->nullable(); // required if withdrawal
            $table->date('occurred_at');
            $table->uuid('client_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'client_id']);
            $table->index(['fund_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_transactions');
    }
};
