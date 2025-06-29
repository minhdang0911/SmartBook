<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Home\BookController;
use App\Http\Controllers\Home\BookFollowController;
use App\Http\Controllers\Home\EbookController;
use App\Http\Controllers\Home\BuybookController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RatingController;

// Lấy thông tin user bằng sanctum (nếu dùng Sanctum thôi)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Tác giả
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/with-books', [AuthorController::class, 'indexWithBooks']);
Route::get('/authors/{id}', [AuthorController::class, 'show']);
Route::get('/authors/{id}/with-books', [AuthorController::class, 'showWithBooks']);

// Danh mục
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/with-books', [CategoryController::class, 'indexWithBooks']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/{id}/with-books', [CategoryController::class, 'showWithBooks']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

// Public routes - không cần đăng nhập
Route::get('/books', [BookController::class, 'index']);
Route::get('/ebooks', [EbookController::class, 'Ebooks']);
Route::get('/buybooks', [BuybookController::class, 'buyBooks']);
Route::get('/books/search', [BookController::class, 'search']);
Route::get('/books/ids', [BookController::class, 'getAllIds']);
Route::get('/test-api', fn () => response()->json(['message' => 'API đang hoạt động ✅']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('/google-redirect', fn () => view('auth.google-redirect'));
Route::get('/login/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::get('/reset-password', function (Request $request) {
    return view('auth.reset-password', [
        'token' => $request->token,
        'email' => $request->email
    ]);
})->name('password.reset');

// Route lấy thông tin người dùng sau đăng nhập
Route::get('/me', [AuthController::class, 'me']);

// Theo dõi sách
Route::get('/books/followed', [BookFollowController::class, 'getFollowedBooksByUser']);
Route::post('/books/follow', [BookFollowController::class, 'follow']);
Route::delete('/books/unfollow', [BookFollowController::class, 'unfollow']);
Route::get('/books/check-follow', [BookFollowController::class, 'checkFollowStatus']);

// Webhook cập nhật trạng thái shipping (public route)
Route::post('/webhook/shipping-status', [OrderController::class, 'updateShippingStatus']);

// Route tạo đơn hàng
Route::middleware(['auth:api'])->post('/orders', [OrderController::class, 'store']);

// Các route cần token bảo mật
Route::middleware(['auth:api'])->group(function () {
    // Đơn hàng
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/admin/orders', [OrderController::class, 'getAllOrders']);
    Route::get('/orders/stats', [OrderController::class, 'getOrderStats']);
    Route::get('/orders/stats/summary', [OrderController::class, 'getOrderStats']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateOrderStatus']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::post('/orders/{orderId}/shipping', [OrderController::class, 'createShipping']);
    Route::get('/orders/{id}/shipping-info', [OrderController::class, 'getShippingInfo']);
    Route::post('/webhook/ghn/shipping-status', [OrderController::class, 'updateShippingStatus']);
    Route::get('/orders/{orderId}/sync-status', [OrderController::class, 'syncOrderStatusFromGHN']);

    // Giỏ hàng
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::post('/item/{cartItemId}', [CartController::class, 'updateCartItem']);
        Route::post('/remove', [CartController::class, 'removeFromCart']);
        Route::delete('/item/{cartItemId}', [CartController::class, 'removeFromCartSingle']);
        Route::delete('/clear', [CartController::class, 'clearCart']);
        Route::get('/count', [CartController::class, 'getCartCount']);
    });

    // Đánh giá sách
    Route::post('/ratings', [RatingController::class, 'store']);
    Route::get('/ratings/book/{bookId}', [RatingController::class, 'getUserRating']);
    Route::get('/ratings/my-ratings', [RatingController::class, 'getUserRatings']);
    Route::delete('/ratings/book/{bookId}', [RatingController::class, 'destroy']);
});

// Đánh giá public
Route::get('/ratings/book/{bookId}/stats', [RatingController::class, 'getBookRatingStats']);
Route::get('/ratings/book/{bookId}/filter', [RatingController::class, 'getRatingsByStar']);

// Lấy chi tiết sách
Route::get('/books/{id}', [BookController::class, 'show']);

// Banner
Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']);
    Route::get('/get', [BannerController::class, 'GetBanner']);
    Route::post('/', [BannerController::class, 'store']);
    Route::get('{id}', [BannerController::class, 'show']);
    Route::put('{id}', [BannerController::class, 'update']);
    Route::delete('{id}', [BannerController::class, 'destroy']);
});

// Mã giảm giá
Route::apiResource('coupons', CouponController::class);
Route::post('/coupons/check', [CouponController::class, 'check']);
Route::get('/coupons/get', [CouponController::class, 'show']);
// Route lấy danh sách mã giảm giá cho người dùng