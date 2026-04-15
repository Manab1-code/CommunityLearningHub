<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_skills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', ['teaching', 'learning']);
            $table->string('name'); // "React", "Python", etc.

            $table->timestamps();

            // avoid duplicates like teaching:React repeated
            $table->unique(['user_id', 'type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_skills');
    }
};
