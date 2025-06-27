<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Home\EbookController;
use App\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;

// Admin Controllers
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\UserController;
// Home Controllers
use App\Http\Controllers\Home\BookController as HomeBookController;

// Auth
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;

// ===================== Public Routes =====================
Route::get('/', function () {
    return view('welcome');
});

// Resource route cho books (web interface)
Route::resource('books', HomeBookController::class);

// ===================== Admin Routes (GIỮ CSRF) =====================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::resource('authors', AuthorController::class);
    Route::resource('publishers', PublisherController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('books', AdminBookController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('orders', OrderController::class);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// ===================== Auth & Google Login =====================
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ===================== Dashboard (User) =====================
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// ===================== User Profile =====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});





// Auth scaffolding
require __DIR__ . '/auth.php';
