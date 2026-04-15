<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\DmCallController;
use App\Http\Controllers\LearningMaterialController;
use App\Http\Controllers\MatchingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SessionRequestController;
use App\Http\Controllers\SkillWalletController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'landing'])->name('landing');

Route::get('/auth/signin', [WebAuthController::class, 'showAuth']);
Route::get('/auth/signup', [WebAuthController::class, 'showAuth']);
Route::get('/auth/{mode}', [WebAuthController::class, 'showAuth']);
Route::post('/auth/login', [WebAuthController::class, 'login'])->name('login');
Route::post('/auth/register', [WebAuthController::class, 'register'])->name('register');
Route::get('/auth/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::middleware('session_token')->group(function () {
    Route::get('/home', [PageController::class, 'home'])->name('home');
    Route::get('/explore', [PageController::class, 'explore'])->name('explore');
    Route::get('/learn', [PageController::class, 'learn'])->name('learn');
    Route::get('/teaching', [PageController::class, 'teaching'])->name('teaching');
    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges');
    Route::post('/challenges/{id}/join', [ChallengeController::class, 'join'])->name('challenges.join');
    Route::get('/messages', [PageController::class, 'messages'])->name('messages');
    Route::get('/messages/with/{userId}', [PageController::class, 'messagesWithUser'])->name('messages.with');
    Route::post('/messages/send', [PageController::class, 'sendMessage'])->name('messages.send');
    Route::get('/communitygroups', [RoomController::class, 'index'])->name('communitygroups');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');
    Route::post('/rooms/{id}/join', [RoomController::class, 'join'])->name('rooms.join');
    Route::post('/conversations/{id}/call/start', [DmCallController::class, 'start'])->name('dm.call.start');
    Route::post('/conversations/{id}/call/end', [DmCallController::class, 'end'])->name('dm.call.end');
    Route::post('/conversations/{id}/call/signal', [DmCallController::class, 'sendSignal'])->name('dm.call.signal');
    Route::get('/conversations/{id}/call/signal/poll', [DmCallController::class, 'poll'])->name('dm.call.signal.poll');
    Route::get('/skillwallet', [SkillWalletController::class, 'index'])->name('skillwallet');
    Route::post('/skillwallet/redeem', [SkillWalletController::class, 'redeem'])->name('skillwallet.redeem');

    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
    Route::get('/update-profile', [PageController::class, 'showUpdateProfile'])->name('update-profile.show');
    Route::post('/profile', [PageController::class, 'updateProfile'])->name('profile.update');

    // Matching routes
    Route::get('/matching/recommendations', [MatchingController::class, 'getRecommendations'])->name('matching.recommendations');
    Route::get('/matching/teachers', [MatchingController::class, 'findTeachers'])->name('matching.teachers');
    Route::get('/matching/learners', [MatchingController::class, 'findLearners'])->name('matching.learners');

    // Session requests routes
    Route::get('/session-requests', [SessionRequestController::class, 'index'])->name('session-requests');
    Route::get('/session-requests/send/{teacherId}', [SessionRequestController::class, 'showCreateForm'])->name('session-requests.create');
    Route::post('/session-requests', [SessionRequestController::class, 'store'])->name('session-requests.store');
    Route::post('/session-requests/{id}/accept', [SessionRequestController::class, 'accept'])->name('session-requests.accept');
    Route::post('/session-requests/{id}/reject', [SessionRequestController::class, 'reject'])->name('session-requests.reject');
    Route::post('/session-requests/{id}/reschedule', [SessionRequestController::class, 'reschedule'])->name('session-requests.reschedule');
    Route::post('/session-requests/{id}/cancel', [SessionRequestController::class, 'cancel'])->name('session-requests.cancel');
    Route::post('/session-requests/{id}/complete', [SessionRequestController::class, 'complete'])->name('session-requests.complete');

    // Notifications routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Learning materials
    Route::get('/learning-materials', [LearningMaterialController::class, 'index'])->name('learning-materials.index');
    Route::get('/learning-materials/create', [LearningMaterialController::class, 'create'])->name('learning-materials.create');
    Route::post('/learning-materials', [LearningMaterialController::class, 'store'])->name('learning-materials.store');
    Route::get('/learning-materials/{id}', [LearningMaterialController::class, 'show'])->name('learning-materials.show');
    Route::post('/learning-materials/{id}/complete', [LearningMaterialController::class, 'markComplete'])->name('learning-materials.complete');
    Route::delete('/learning-materials/{id}', [LearningMaterialController::class, 'destroy'])->name('learning-materials.destroy');
});

// Admin panel (fixed admin: admin@gmail.com / admin@12345)
Route::middleware(['session_token', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/session-requests', [AdminController::class, 'sessionRequests'])->name('session-requests');
    Route::get('/learning-materials', [AdminController::class, 'learningMaterials'])->name('learning-materials');
    Route::get('/challenges', [AdminController::class, 'challenges'])->name('challenges');
    Route::get('/challenges/create', [AdminController::class, 'createChallenge'])->name('challenges.create');
    Route::post('/challenges', [AdminController::class, 'storeChallenge'])->name('challenges.store');
    Route::get('/challenges/{id}/edit', [AdminController::class, 'editChallenge'])->name('challenges.edit');
    Route::post('/challenges/{id}', [AdminController::class, 'updateChallenge'])->name('challenges.update');
});

Route::get('/forgot-password', fn () => redirect('/auth/signin'));
