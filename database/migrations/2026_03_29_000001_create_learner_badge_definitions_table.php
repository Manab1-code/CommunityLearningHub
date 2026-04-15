<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learner_badge_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name', 120);
            $table->string('description', 500);
            $table->string('category', 32); // hours, modules, rating
            $table->string('icon_emoji', 16)->default('🏅');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['slug' => 'first_hour', 'name' => 'First Hour', 'description' => 'Completed your first learning session (~1 hr).', 'category' => 'hours', 'icon_emoji' => '🎯', 'sort_order' => 10],
            ['slug' => 'five_hours', 'name' => 'Dedicated Learner', 'description' => 'Completed 5 learning sessions (~5 hrs).', 'category' => 'hours', 'icon_emoji' => '📚', 'sort_order' => 20],
            ['slug' => 'ten_hours', 'name' => 'Learning Veteran', 'description' => 'Completed 10 learning sessions (~10 hrs).', 'category' => 'hours', 'icon_emoji' => '🎓', 'sort_order' => 30],
            ['slug' => 'first_module', 'name' => 'Module Starter', 'description' => 'Marked your first community learning module as complete.', 'category' => 'modules', 'icon_emoji' => '📖', 'sort_order' => 40],
            ['slug' => 'five_modules', 'name' => 'Module Explorer', 'description' => 'Completed 5 learning modules.', 'category' => 'modules', 'icon_emoji' => '🧭', 'sort_order' => 50],
            ['slug' => 'fifteen_modules', 'name' => 'Curriculum Champion', 'description' => 'Completed 15 learning modules.', 'category' => 'modules', 'icon_emoji' => '🏆', 'sort_order' => 60],
            ['slug' => 'highly_rated', 'name' => 'Highly Rated', 'description' => 'Average tutor rating of 4.5+ (at least 3 rated sessions).', 'category' => 'rating', 'icon_emoji' => '⭐', 'sort_order' => 70],
            ['slug' => 'star_learner', 'name' => 'Star Learner', 'description' => 'Average tutor rating of 4.8+ (at least 5 rated sessions).', 'category' => 'rating', 'icon_emoji' => '🌟', 'sort_order' => 80],
        ];

        foreach ($rows as $r) {
            DB::table('learner_badge_definitions')->insert(array_merge($r, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('learner_badge_definitions');
    }
};
