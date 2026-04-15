<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('learner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('skill_name'); // The skill being requested
            $table->enum('skill_level', ['beginner', 'intermediate', 'expert'])->nullable();

            $table->text('message')->nullable(); // Optional message from learner
            $table->enum('status', ['pending', 'accepted', 'rejected', 'rescheduled', 'completed', 'cancelled'])
                ->default('pending');

            $table->datetime('proposed_date')->nullable(); // Learner's proposed date/time
            $table->datetime('accepted_date')->nullable(); // Teacher's accepted/rescheduled date/time
            $table->text('rejection_reason')->nullable(); // If rejected, optional reason
            $table->text('reschedule_reason')->nullable(); // If rescheduled, reason

            $table->timestamps();

            // Indexes for faster queries
            $table->index(['learner_id', 'status']);
            $table->index(['teacher_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_requests');
    }
};
