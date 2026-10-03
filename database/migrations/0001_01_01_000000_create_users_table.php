<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->enum('marital_status', ['single', 'married'])->nullable();
            $table->smallInteger('dependents_count')->default(0);
            $table->enum('income_stability', ['stable', 'variable'])->nullable();
            $table->boolean('has_installments')->default(false);
            $table->string('timezone', 40)->default('Asia/Jakarta');
            // primary_income_id FK ditambah via migration terpisah (setelah incomes dibuat)
            $table->uuid('primary_income_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

