<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// my custom part start
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController; 
use App\Http\Controllers\Admin\PostController;
use App\Exports\UserStatisticsExport;
use App\Exports\UserExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Admin\ExportController;
// my custom part end

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth','admin'])->group(function () {
// my custom part start
Route::get('dashboard', [AdminController::class, 'index'])->name('dashboard');

Route::get('dashboard/user', [UserController::class, 'index']);
Route::get('dashboard/user/add', [UserController::class, 'add']);
Route::get('dashboard/user/edit/{id}', [UserController::class, 'edit']);
Route::get('dashboard/user/view/{id}', [UserController::class, 'view']);
Route::post('dashboard/user/submit', [UserController::class, 'insert']);
//Route::post('dashboard/user/update', [UserController::class, 'update']);
Route::put('user/{id}', [UserController::class, 'update'])->name('user.update');
Route::post('dashboard/user/softdelete', [UserController::class, 'softdelete']);
Route::post('dashboard/user/restore', [UserController::class, 'restore']);
Route::post('dashboard/user/delete', [UserController::class, 'delete']);
 

Route::get('dashboard/posts', [PostController::class, 'index']);
Route::get('dashboard/post/view/{id}', [PostController::class, 'show']);
Route::get('dashboard/post/approve/{id}', [PostController::class, 'approve'])->name('post.approve');
Route::post('dashboard/post/decline/{id}', [PostController::class, 'decline'])->name('post.decline');
Route::post('dashboard/post/delete', [PostController::class, 'destroy']);  

Route::get('/export_users', function () {
    return Excel::download(new UserExport, 'users.xlsx');
});
Route::get('/export-statistics', [ExportController::class, 'exportUserStatistics'])->name('export.statistics');
});
require __DIR__.'/auth.php';
