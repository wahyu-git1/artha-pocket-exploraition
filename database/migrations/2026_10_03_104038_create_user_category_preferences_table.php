<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hasil koreksi kategori per pengguna untuk Smart Entry
        Schema::create('user_category_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('keyword', 60); // kata kunci yang dikoreksi user
            $table->uuid('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->integer('hit_count')->default(0); // berapa kali dipakai
            $table->timestamps();
            $table->softDeletes();

            // Satu keyword per user
            $table->unique(['user_id', 'keyword']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_category_preferences');
    }
};
