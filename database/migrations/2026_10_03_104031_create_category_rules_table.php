<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kamus kata kunci global untuk Smart Entry kategorisasi otomatis
        // Tidak memakai soft delete & timestamps sederhana
        Schema::create('category_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('keyword', 60); // e.g. "bakso", "kopi", "ojek"
            $table->uuid('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->smallInteger('priority')->default(0); // pemenang jika keyword ganda
            $table->timestamps();

            $table->index('keyword');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_rules');
    }
};
