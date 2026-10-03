<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // user_id = NULL berarti kategori default sistem
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('name', 60);
            $table->enum('type', ['expense', 'income']);
            // bucket untuk Fase 4 alokasi
            $table->enum('bucket', ['need', 'want'])->nullable();
            $table->string('icon', 40)->nullable();
            $table->char('color', 7)->nullable(); // hex code e.g. #FF5733
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Satu user tidak boleh punya kategori dengan nama & tipe yang sama
            $table->unique(['user_id', 'name', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
