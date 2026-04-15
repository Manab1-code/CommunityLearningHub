<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share pending session requests count and unread notifications count with navbar
        // Share current user's timezone so message times and dates show in their location/country
        view()->composer('*', function ($view) {
            $tz = auth()->check() && auth()->user()->profile?->timezone
                ? auth()->user()->profile->timezone
                : config('app.timezone');
            $view->with('userTimezone', $tz);
        });

        view()->composer('partials.navbar2', function ($view) {
            $pendingCount = 0;
            $unreadNotificationsCount = 0;
            $unreadMessagesCount = 0;
            $navProfilePhotoUrl = null;
            $navProfileInitial = 'U';
            if (auth()->check()) {
                $user = auth()->user();
                $profile = $user->profile;
                $navProfilePhotoUrl = $profile?->photo_path
                    ? asset('storage/'.$profile->photo_path)
                    : null;
                $navProfileInitial = strtoupper(mb_substr(trim((string) $user->name), 0, 1)) ?: 'U';

                $pendingCount = \App\Models\SessionRequest::where('teacher_id', $user->id)
                    ->where('status', 'pending')
                    ->count();

                $unreadNotificationsCount = \App\Models\Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();

                $unreadMessagesCount = \App\Models\Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->where('type', 'new_message')
                    ->count();
            }
            $view->with('pendingRequestsCount', $pendingCount);
            $view->with('unreadNotificationsCount', $unreadNotificationsCount);
            $view->with('unreadMessagesCount', $unreadMessagesCount);
            $view->with('navProfilePhotoUrl', $navProfilePhotoUrl);
            $view->with('navProfileInitial', $navProfileInitial);
        });
    }
}
