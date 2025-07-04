<?php

use App\Http\Controllers\RevenueController;
use Illuminate\Support\Facades\Route;

// Admin Controllers
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\BookImageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\TopicController;

// Home Controllers
use App\Http\Controllers\Home\BookController as HomeBookController;
use Cloudinary\Configuration\Configuration;


// Auth
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


Route::get('/upload-form', function () {
    return '
    <form method="POST" enctype="multipart/form-data" action="/upload-test">
        ' . csrf_field() . '
        <input type="file" name="image" required>
        <button>Upload</button>
    </form>';
});

Route::post('/upload-test', function (Request $request) {
    $url = Cloudinary::upload($request->file('image')->getRealPath(), [
        'folder' => 'test'
    ])->getSecurePath();

    return "Uploaded to: <a href='$url' target='_blank'>$url</a>";
});
// ===================== Public Routes =====================
Route::get('/', function () {
    return view('welcome');
});

Route::get('/check-cloud', function () {
    return Configuration::instance()->cloud;
});


Route::get('/debug-cloudinary', function () {
    return Configuration::instance()->cloud;
});


// Resource route cho books (web interface)
Route::resource('books', HomeBookController::class);

// ===================== Admin Routes =====================

// =====================Admin routes=====================
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::put('/{user}/status', [UserController::class, 'toggleStatus'])->name('toggleStatus');
        Route::put('/{user}/lock', [UserController::class, 'lock'])->name('lock');
        Route::put('/{user}/unlock', [UserController::class, 'unlock'])->name('unlock');
    });

    // Book Images
    Route::prefix('book-images')->name('book_images.')->group(function () {
        Route::get('/', [BookImageController::class, 'index'])->name('index');
        Route::get('/create', [BookImageController::class, 'create'])->name('create');
        Route::post('/', [BookImageController::class, 'store'])->name('store');
        Route::get('/{book_image}/edit', [BookImageController::class, 'edit'])->name('edit');
        Route::put('/{book_image}', [BookImageController::class, 'update'])->name('update');
        Route::delete('/{book_image}', [BookImageController::class, 'destroy'])->name('destroy');
    });

    // Resource controllers
    Route::resource('authors', AuthorController::class);
    Route::resource('publishers', PublisherController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('books', AdminBookController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('coupons', CouponController::class);
    Route::resource('topics', TopicController::class);
    Route::resource('posts', PostController::class);
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

Route::post('/admin/books/upload', [BookController::class, 'upload'])->name('admin.books.upload');
Route::post('/ckeditor/upload', [App\Http\Controllers\CKEditorController::class, 'upload'])->name('ckeditor.upload');

Route::get('/admin/revenue', [RevenueController::class, 'index'])->name('admin.revenue.index');



// Auth scaffolding
require __DIR__ . '/auth.php';
