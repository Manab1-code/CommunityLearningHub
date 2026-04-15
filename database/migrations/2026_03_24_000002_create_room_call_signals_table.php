<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_call_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30); // offer / answer / ice / hangup / ready
            $table->longText('payload')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'id']);
            $table->index(['target_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_call_signals');
    }
};
