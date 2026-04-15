<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // video, notes, guide
            $table->string('skill_name')->nullable();

            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('url')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('skill_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_materials');
    }
};
