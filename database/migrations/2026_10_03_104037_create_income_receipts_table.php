<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penerimaan aktual dari sebuah sumber pendapatan
        // e.g. "Gajian bulan Oktober Rp 6.500.000"
        Schema::create('income_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('income_id');
            $table->foreign('income_id')->references('id')->on('incomes')->cascadeOnDelete();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('category_id')->nullable(); // kategori tipe income
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->bigInteger('amount'); // > 0, dalam rupiah
            $table->date('received_at');
            $table->string('note', 255)->nullable();
            $table->text('raw_input')->nullable(); // kalimat asli Smart Entry
            $table->uuid('client_id')->nullable(); // untuk idempotensi sinkronisasi
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'client_id']); // idempotensi
            $table->index(['income_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_receipts');
    }
};
