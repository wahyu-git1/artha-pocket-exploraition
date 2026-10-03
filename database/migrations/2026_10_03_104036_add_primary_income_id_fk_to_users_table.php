<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan FK constraint setelah tabel incomes dibuat
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('primary_income_id')
                ->references('id')
                ->on('incomes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['primary_income_id']);
        });
    }
};
