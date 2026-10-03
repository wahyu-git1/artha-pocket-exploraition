<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('income_id');
            $table->foreign('income_id')->references('id')->on('incomes')->restrictOnDelete();
            $table->uuid('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->string('item', 150); // e.g. "Bakso"
            $table->bigInteger('amount'); // > 0, dalam rupiah
            $table->date('spent_at');
            $table->string('note', 255)->nullable();
            $table->text('raw_input')->nullable();         // kalimat asli Smart Entry
            $table->decimal('confidence_score', 3, 2)->nullable(); // 0.00 - 1.00
            $table->enum('source', ['manual', 'smart_entry', 'sync'])->default('manual');
            $table->uuid('client_id')->nullable(); // untuk idempotensi sinkronisasi
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'client_id']); // idempotensi
            $table->index(['user_id', 'spent_at']);   // query utama
            $table->index(['user_id', 'category_id']); // laporan per kategori
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
