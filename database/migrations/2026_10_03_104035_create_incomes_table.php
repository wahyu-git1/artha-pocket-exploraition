<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('name', 100); // e.g. "Gaji", "Freelance Tokopedia"
            $table->bigInteger('default_amount')->nullable(); // nominal biasa, nullable
            $table->enum('frequency', ['monthly', 'weekly', 'irregular']);
            $table->smallInteger('pay_day')->nullable(); // 1-31, hari gajian
            $table->boolean('is_primary')->default(false); // hanya 1 per user
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
