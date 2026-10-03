<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('income_id')->nullable();
            $table->foreign('income_id')->references('id')->on('incomes')->restrictOnDelete();
            $table->enum('instrument_type', ['mutual_fund', 'gold', 'stock', 'bond', 'deposit', 'crypto', 'other']);
            $table->string('instrument_name', 100);
            $table->bigInteger('amount'); // > 0
            $table->date('invested_at');
            $table->string('note', 255)->nullable();
            $table->uuid('client_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'client_id']);
            $table->index(['user_id', 'invested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
