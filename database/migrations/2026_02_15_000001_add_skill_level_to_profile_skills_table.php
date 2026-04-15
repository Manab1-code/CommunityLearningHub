<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_skills', function (Blueprint $table) {
            $table->enum('skill_level', ['beginner', 'intermediate', 'expert'])->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('profile_skills', function (Blueprint $table) {
            $table->dropColumn('skill_level');
        });
    }
};
