<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\LocalDiscoveryController;
use App\Http\Controllers\MatchingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionRequestController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('api_token')->group(function () {

    // profile
    Route::get('/me/profile', [ProfileController::class, 'me']);
    Route::post('/profile', [ProfileController::class, 'upsert']);

    // ✅ messenger
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations/start', [ConversationController::class, 'start']);

    Route::get('/conversations/{id}/messages', [MessageController::class, 'list']);
    Route::post('/conversations/{id}/messages', [MessageController::class, 'send']);

    // ✅ skill matching
    Route::get('/matching/recommendations', [MatchingController::class, 'getRecommendations']);
    Route::get('/matching/teachers', [MatchingController::class, 'findTeachers']);
    Route::get('/matching/learners', [MatchingController::class, 'findLearners']);

    // ✅ local discovery – find skill opportunities, filter by categories & distance
    Route::get('/discovery/opportunities', [LocalDiscoveryController::class, 'opportunities']);
    Route::get('/discovery/categories', [LocalDiscoveryController::class, 'categories']);

    // ✅ session requests
    Route::get('/session-requests', [SessionRequestController::class, 'index']);
    Route::post('/session-requests', [SessionRequestController::class, 'store']);
    Route::post('/session-requests/{id}/accept', [SessionRequestController::class, 'accept']);
    Route::post('/session-requests/{id}/reject', [SessionRequestController::class, 'reject']);
    Route::post('/session-requests/{id}/reschedule', [SessionRequestController::class, 'reschedule']);
    Route::post('/session-requests/{id}/cancel', [SessionRequestController::class, 'cancel']);

    // ✅ notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});
