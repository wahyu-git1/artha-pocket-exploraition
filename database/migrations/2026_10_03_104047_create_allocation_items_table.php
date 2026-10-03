<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plan_id');
            $table->foreign('plan_id')->references('id')->on('allocation_plans')->cascadeOnDelete();
            $table->enum('bucket', ['need', 'want', 'saving', 'investment']);
            $table->decimal('percent', 5, 2);
            $table->timestamps();

            $table->unique(['plan_id', 'bucket']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_items');
    }
};
