<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\MessageController;
// use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
 
});
Route::get('/validate-token', [AuthController::class, 'validateToken']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('rooms', [RoomController::class, 'store']); // Add Room
    Route::get('rooms', [RoomController::class, 'index']);  // Display Rooms
    Route::post('rooms/{id}', [RoomController::class, 'update']);  // Update Rooms
    Route::get('user_rooms', [RoomController::class, 'getUserRooms']);
    Route::get('/rooms/search', [RoomController::class, 'search']);

    Route::post('auth/logout', [AuthController::class, 'logout']);
     Route::post('auth/change-password', [AuthController::class, 'changePassword']);
    // Route::get('user/{user_id}', [UserController::class, 'getUserById']);

    //favorites
    Route::post('/favorites/add', [FavoriteController::class, 'addFavorite']);
    Route::post('/favorites/remove', [FavoriteController::class, 'removeFavorite']);
    Route::get('/favorites', [FavoriteController::class, 'listFavorites']);
    Route::post('/post_favorite', [FavoriteController::class, 'listFavoritePost']);

    Route::put('/user/update-field', [ProfileController::class, 'updateField']);
    Route::post('/user/verify-and-update-email', [ProfileController::class, 'verifyAndUpdateEmail']);
    Route::post('/user/update-profile-image', [ProfileController::class, 'updateProfileImage']);  
    Route::get('/user/counts', [ProfileController::class, 'getCounts']);

    Route::get('/messages/{peerId}', [MessageController::class, 'getMessages']);
    Route::get('/message/{messageId}', [MessageController::class, 'getMessage']);
    Route::get('/conversations/{userId}', [MessageController::class,'getConversations']);
    Route::post('/mark_read/{message_id}', [MessageController::class,'markAsRead']);
    Route::post('/messages/send', [MessageController::class,'sendMessage']);
    Route::get('/users_messages', [MessageController::class,'getUsers']);
    Route::post('/upload-file', [MessageController::class, 'uploadFile']);

    Route::prefix('notifications')->group(function () {
    Route::post('/', [NotificationController::class, 'store']); // Add notification
    Route::get('/', [NotificationController::class, 'index']); // Get user notifications
    Route::put('/{notificationId}/mark-as-read', [NotificationController::class, 'markAsRead']); // Mark as read
});

});
