<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('type'); // weekly, community
            $table->string('target_type'); // teach_sessions, attend_sessions, share_resources, complete_sessions, give_feedback
            $table->unsignedInteger('target_count');
            $table->unsignedInteger('points')->default(0);
            $table->string('icon')->nullable(); // emoji or icon name
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
