<?php

use App\Http\Controllers\Api\BookImageController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventProductController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\TopicApiController;
use App\Http\Controllers\RevenueController;
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
use App\Http\Controllers\BookImageController as ControllersBookImageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\Api\BookChapterApiController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentReactionController;
use App\Http\Controllers\PostLikeController;





Route::prefix('comments')->group(function () {
    Route::get('/{post}', [CommentController::class, 'index']);
    Route::post('/', [CommentController::class, 'store']);
    Route::put('/{id}', [CommentController::class, 'update']);
    Route::delete('/{id}', [CommentController::class, 'destroy']);
});

Route::prefix('comments')->group(function () {
    Route::post('/{id}/react', [CommentReactionController::class, 'react']);
    Route::post('/{id}/unpreact', [CommentReactionController::class, 'unreact']);
    Route::get('/{id}/reactions', [CommentReactionController::class, 'listReactions']);

});

// Lấy thông tin user bằng sanctum (nếu dùng Sanctum thôi)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/books', [BookChapterApiController::class, 'listBooks']);
Route::get('/books/{bookId}/chapters', [BookChapterApiController::class, 'getChapters']);
Route::get('/chapters/{chapterId}', [BookChapterApiController::class, 'getChapter']);


// Bài viết
Route::prefix('posts')->group(function () {
    Route::get('/', [PostApiController::class, 'index']);
    Route::get('liked', [PostLikeController::class, 'getLikedPosts']);
    Route::get('/pinned', [PostApiController::class, 'pinned']); // bài viết được ghim
    Route::get('/popular', [PostApiController::class, 'popular']); // theo views
    Route::get('/featured', [PostApiController::class, 'featured']); // theo lượt thích
    Route::post('/{post}/like', [PostApiController::class, 'like']);  // like bài viết
    Route::delete('/{post}/unlike', [PostApiController::class, 'unlike']); // unlike bài viết
    Route::get('/related/{topicId}', [PostApiController::class, 'related']);
    Route::get('/{slug}', [PostApiController::class, 'show']);
    // Lấy danh sách tất cả các bài viết/cuốn sách đã like



});

// Chủ đề bài viết
Route::prefix('topics')->group(function () {
    Route::get('/', [TopicApiController::class, 'index']);
    Route::get('/{slug}/posts', [TopicApiController::class, 'posts']);
});

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

// books
Route::get('/books', [BookController::class, 'index']);
Route::get('/ebooks', [EbookController::class, 'Ebooks']);
Route::get('/buybooks', [BuybookController::class, 'buyBooks']);
Route::get('/books/search', [BookController::class, 'search']);
Route::get('/books/ids', [BookController::class, 'getAllIds']);
Route::post('/books/increase-view/{id}', [BookController::class, 'increaseView']);

Route::get('/test-api', fn() => response()->json(['message' => 'API đang hoạt động ✅']));
Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('/google-redirect', fn() => view('auth.google-redirect'));
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

// routes/api.php
Route::prefix('coupons')->group(function () {
    Route::post('check', [CouponController::class, 'check']);
    Route::get('get', [CouponController::class, 'show']);
    Route::post('/', [CouponController::class, 'store']);
    Route::get('{id}', [CouponController::class, 'detail']);
    Route::patch('{coupon}', [CouponController::class, 'update']); // Sửa {id} thành {coupon}
    Route::post('{coupon}', [CouponController::class, 'destroy']); // Sửa {id} thành {coupon}
});
// Mã giảm giá

// Route lấy danh sách mã giảm giá cho người dùng
//
Route::get('/books/{book_id}/images', [ControllersBookImageController::class, 'getImagesByBookId']);
// ZaloPay routes
Route::prefix('/orders/zalopay')->group(function () {
    // Test ZaloPay connection
    Route::get('/test-zalopay', function () {
        $config = [
            'app_id' => env('ZALOPAY_APP_ID'),
            'key1' => env('ZALOPAY_KEY1'),
            'endpoint' => env('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create'),
        ];

        // Kiểm tra config trước
        if (!$config['app_id'] || !$config['key1']) {
            return response()->json([
                'error' => 'ZaloPay config missing',
                'app_id' => $config['app_id'] ? 'OK' : 'MISSING',
                'key1' => $config['key1'] ? 'OK' : 'MISSING',
                'endpoint' => $config['endpoint']
            ], 400);
        }

        // Test data đơn giản
        $app_trans_id = date('ymd') . '_' . rand(100000, 999999);
        $amount = 50000;
        $app_time = time() * 1000;

        $embed_data = json_encode(['redirecturl' => 'https://sandbox.zalopay.vn/thankyou']);
        $item = json_encode([['itemid' => 'test', 'itemname' => 'Test Item', 'itemprice' => $amount, 'itemquantity' => 1]]);

        $data = $config['app_id'] . '|' . $app_trans_id . '|demo|' . $amount . '|' . $app_time . '|' . $embed_data . '|' . $item;
        $mac = hash_hmac('sha256', $data, $config['key1']);

        $order = [
            'app_id' => $config['app_id'],
            'app_trans_id' => $app_trans_id,
            'app_user' => 'demo',
            'app_time' => $app_time,
            'amount' => $amount,
            'item' => $item,
            'embed_data' => $embed_data,
            'description' => 'Test order',
            'callback_url' => url('/api/test-callback'),
            'mac' => $mac,
        ];

        Log::info('Test ZaloPay Request:', $order);
        Log::info('Data for MAC:', ['data' => $data]);

        try {
            $response = Http::timeout(30)
                ->asForm()
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                    'User-Agent' => 'SmartBook/1.0'
                ])
                ->post($config['endpoint'], $order);

            $responseBody = $response->body();
            $isHtml = str_contains($responseBody, '<html>') || str_contains($responseBody, '<!DOCTYPE');

            Log::info('ZaloPay Response:', [
                'status' => $response->status(),
                'body' => $responseBody,
                'is_html' => $isHtml
            ]);

            return response()->json([
                'config' => [
                    'app_id' => $config['app_id'],
                    'endpoint' => $config['endpoint'],
                    'key1_length' => strlen($config['key1'])
                ],
                'request' => $order,
                'response' => [
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                    'body' => $responseBody,
                    'is_html' => $isHtml,
                    'is_json' => !$isHtml && json_decode($responseBody) !== null
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('ZaloPay Test Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Request failed',
                'message' => $e->getMessage(),
                'config' => [
                    'app_id' => $config['app_id'],
                    'endpoint' => $config['endpoint']
                ]
            ], 500);
        }
    });
    // Route tạo đơn hàng ZaloPay

    // Route tạo đơn hàng ZaloPay
    Route::post('/create-order', function (Request $request) {
        $config = [
            'app_id' => env('ZALOPAY_APP_ID'),
            'key1' => env('ZALOPAY_KEY1'),
            'key2' => env('ZALOPAY_KEY2'),
            'endpoint' => env('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create'),
        ];

        // Validate config
        if (!$config['app_id'] || !$config['key1']) {
            return response()->json([
                'error' => 'ZaloPay configuration missing',
                'return_code' => 0
            ], 400);
        }

        // Validate request
        $request->validate([
            'amount' => 'required|integer|min:1000',
        ]);

        try {
            $amount = $request->input('amount');
            $description = $request->input('description', 'Thanh toán đơn hàng');
            $user_id = $request->input('user_id', 'demo');

            // Tạo app_trans_id theo format YYMMDD_XXXXXX
            $app_trans_id = date('ymd') . '_' . rand(100000, 999999);
            $app_time = time() * 1000; // ZaloPay yêu cầu timestamp milliseconds

            // Embed data
            $embed_data = [
                'redirecturl' => 'http://localhost:3000/'
            ];

            // Items
            $items = [
                [
                    'itemid' => 'item_' . time(),
                    'itemname' => $description,
                    'itemprice' => $amount,
                    'itemquantity' => 1
                ]
            ];

            // Tạo MAC theo format chính xác của ZaloPay
            // Format: app_id|app_trans_id|app_user|amount|app_time|embed_data|item
            $data = $config['app_id'] . '|' . $app_trans_id . '|' . $user_id . '|' . $amount . '|' . $app_time . '|' . json_encode($embed_data) . '|' . json_encode($items);
            $mac = hash_hmac('sha256', $data, $config['key1']);

            // Tạo order
            $order = [
                'app_id' => $config['app_id'],
                'app_trans_id' => $app_trans_id,
                'app_user' => $user_id,
                'app_time' => $app_time,
                'amount' => $amount,
                'item' => json_encode($items),
                'embed_data' => json_encode($embed_data),
                'description' => $description,
                'callback_url' => url('/api/zalopay/callback'),
                'mac' => $mac,
            ];

            Log::info('ZaloPay Create Order Request:', [
                'order' => $order,
                'mac_data' => $data
            ]);

            // Gửi request tới ZaloPay
            $response = Http::timeout(30)
                ->asForm()
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json'
                ])
                ->post($config['endpoint'], $order);

            $responseData = $response->json();

            Log::info('ZaloPay Create Order Response:', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->successful() && isset($responseData['return_code']) && $responseData['return_code'] == 1) {
                return response()->json([
                    'success' => true,
                    'return_code' => $responseData['return_code'],
                    'return_message' => $responseData['return_message'],
                    'order_url' => $responseData['order_url'] ?? null,
                    'zp_trans_token' => $responseData['zp_trans_token'] ?? null,
                    'app_trans_id' => $app_trans_id
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'return_code' => $responseData['return_code'] ?? 0,
                    'return_message' => $responseData['return_message'] ?? 'Tạo đơn hàng thất bại',
                    'sub_return_code' => $responseData['sub_return_code'] ?? null,
                    'sub_return_message' => $responseData['sub_return_message'] ?? null
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('ZaloPay Create Order Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tạo đơn hàng',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // Route callback từ ZaloPay
    // Route::post('/callback', function (Request $request) {
    //     $config = [
    //         'key2' => env('ZALOPAY_KEY2')
    //     ];

    //     Log::info('ZaloPay Callback Received:', $request->all());

    //     try {
    //         $data = $request->input('data');
    //         $receivedMac = $request->input('mac');

    //         // if (!$data || !$receivedMac) {
    //         //     return response()->json([
    //         //         'return_code' => 0,
    //         //         'return_message' => 'Invalid callback data'
    //         //     ]);
    //         // }

    //         // Verify MAC
    //         $computedMac = hash_hmac('sha256', $data, $config['key2']);

    //         // if ($computedMac !== $receivedMac) {
    //         //     Log::warning('ZaloPay Callback Invalid MAC:', [
    //         //         'received_mac' => $receivedMac,
    //         //         'computed_mac' => $computedMac,
    //         //         'data' => $data
    //         //     ]);

    //         //     return response()->json([
    //         //         'return_code' => -1,
    //         //         'return_message' => 'mac not equal'
    //         //     ]);
    //         // }

    //         // Parse callback data
    //         $callbackData = json_decode($data, true);

    //         if (!$callbackData) {
    //             return response()->json([
    //                 'return_code' => 0,
    //                 'return_message' => 'Invalid JSON data'
    //             ]);
    //         }

    //         Log::info('ZaloPay Payment Success:', [
    //             'app_trans_id' => $callbackData['app_trans_id'] ?? null,
    //             'zp_trans_id' => $callbackData['zp_trans_id'] ?? null,
    //             'amount' => $callbackData['amount'] ?? null
    //         ]);

    //         // TODO: Xử lý logic thanh toán thành công ở đây
    //         // Ví dụ: Cập nhật trạng thái đơn hàng trong database

    //         return response()->json([
    //             'return_code' => 1,
    //             'return_message' => 'success'
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('ZaloPay Callback Error:', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'return_code' => 0,
    //             'return_message' => 'exception'
    //         ]);
    //     }
    // });

    // Route kiểm tra trạng thái đơn hàng
    Route::post('/check-status', function (Request $request) {
        $config = [
            'app_id' => env('ZALOPAY_APP_ID'),
            'key1' => env('ZALOPAY_KEY1'),
            'query_endpoint' => env('ZALOPAY_QUERY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/query'),
        ];

        $request->validate([
            'app_trans_id' => 'required|string'
        ]);

        try {
            $app_trans_id = $request->input('app_trans_id');

            // Tạo MAC cho query
            $data = $config['app_id'] . '|' . $app_trans_id . '|' . $config['key1'];
            $mac = hash_hmac('sha256', $data, $config['key1']);

            $queryData = [
                'app_id' => $config['app_id'],
                'app_trans_id' => $app_trans_id,
                'mac' => $mac
            ];

            Log::info('ZaloPay Check Status Request:', [
                'query_data' => $queryData,
                'mac_data' => $data
            ]);

            $response = Http::timeout(30)
                ->asForm()
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json'
                ])
                ->post($config['query_endpoint'], $queryData);

            $responseData = $response->json();

            Log::info('ZaloPay Check Status Response:', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            return response()->json($responseData);
        } catch (\Exception $e) {
            Log::error('ZaloPay Check Status Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'return_code' => 0,
                'return_message' => 'Có lỗi xảy ra khi kiểm tra trạng thái đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    });

    // Route test ZaloPay (để debug)
    // Route::get('/zalopay/test', function () {
    //     $config = [
    //         'app_id' => env('ZALOPAY_APP_ID'),
    //         'key1' => env('ZALOPAY_KEY1'),
    //         'key2' => env('ZALOPAY_KEY2'),
    //         'endpoint' => env('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create'),
    //     ];

    //     // Kiểm tra config
    //     $configStatus = [
    //         'app_id' => $config['app_id'] ? 'OK (' . $config['app_id'] . ')' : 'MISSING',
    //         'key1' => $config['key1'] ? 'OK (length: ' . strlen($config['key1']) . ')' : 'MISSING',
    //         'key2' => $config['key2'] ? 'OK (length: ' . strlen($config['key2']) . ')' : 'MISSING',
    //         'endpoint' => $config['endpoint']
    //     ];

    //     return response()->json([
    //         'status' => 'ZaloPay Test Configuration',
    //         'config' => $configStatus,
    //         'callback_url' => url('/api/zalopay/callback'),
    //         'test_endpoint' => url('/api/zalopay/create-order'),
    //         'check_endpoint' => url('/api/zalopay/check-status'),
    //         'sample_request' => [
    //             'amount' => 50000,
    //             'description' => 'Test payment',
    //             'user_id' => 'demo'
    //         ]
    //     ]);
    // });

    // Route refund (hoàn tiền)
    // Route::post('/zalopay/refund', function (Request $request) {
    //     $config = [
    //         'app_id' => env('ZALOPAY_APP_ID'),
    //         'key1' => env('ZALOPAY_KEY1'),
    //         'refund_endpoint' => env('ZALOPAY_REFUND_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/refund'),
    //     ];

    //     $request->validate([
    //         'zp_trans_id' => 'required|string',
    //         'amount' => 'required|integer|min:1000',
    //         'description' => 'string|max:255'
    //     ]);

    //     try {
    //         $zp_trans_id = $request->input('zp_trans_id');
    //         $amount = $request->input('amount');
    //         $description = $request->input('description', 'Hoàn tiền đơn hàng');

    //         $m_refund_id = date('ymd') . '_' . $config['app_id'] . '_' . rand(100000, 999999);
    //         $timestamp = time() * 1000;

    //         // Tạo MAC cho refund
    //         $data = $config['app_id'] . '|' . $zp_trans_id . '|' . $amount . '|' . $description . '|' . $timestamp;
    //         $mac = hash_hmac('sha256', $data, $config['key1']);

    //         $refundData = [
    //             'app_id' => $config['app_id'],
    //             'zp_trans_id' => $zp_trans_id,
    //             'amount' => $amount,
    //             'description' => $description,
    //             'timestamp' => $timestamp,
    //             'm_refund_id' => $m_refund_id,
    //             'mac' => $mac
    //         ];

    //         Log::info('ZaloPay Refund Request:', [
    //             'refund_data' => $refundData,
    //             'mac_data' => $data
    //         ]);

    //         $response = Http::timeout(30)
    //             ->asForm()
    //             ->withHeaders([
    //                 'Content-Type' => 'application/x-www-form-urlencoded',
    //                 'Accept' => 'application/json'
    //             ])
    //             ->post($config['refund_endpoint'], $refundData);

    //         $responseData = $response->json();

    //         Log::info('ZaloPay Refund Response:', [
    //             'status' => $response->status(),
    //             'response' => $responseData
    //         ]);

    //         return response()->json($responseData);

    //     } catch (\Exception $e) {
    //         Log::error('ZaloPay Refund Error:', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'return_code' => 0,
    //             'return_message' => 'Có lỗi xảy ra khi hoàn tiền',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // });

});
// Hoặc bạn có thể group với middleware auth nếu cần xác thực
Route::prefix('revenue')->group(function () {
    Route::get('/total', [RevenueController::class, 'getTotalRevenue']);
    Route::get('/daily', [RevenueController::class, 'getDailyRevenue']);
    Route::get('/monthly', [RevenueController::class, 'getMonthlyRevenue']);
    Route::get('/quarterly', [RevenueController::class, 'getQuarterlyRevenue']);
    Route::get('/yearly', [RevenueController::class, 'getYearlyRevenue']);
    Route::get('/top-products', [RevenueController::class, 'getTopProducts']);
    Route::get('/by-status', [RevenueController::class, 'getRevenueByStatus']);
    Route::get('/by-payment', [RevenueController::class, 'getRevenueByPaymentMethod']);
    Route::get('/dashboard', [RevenueController::class, 'getDashboard']);
    // Route riêng cho từng quý
    Route::get('/quarter', [RevenueController::class, 'getQuarterDetail']);
});




// Route quản lý sách thuộc về event
Route::prefix('events/{event_id}')->group(function () {
    Route::post('/books', [EventProductController::class, 'store']);
    Route::put('/books/{book_id}', [EventProductController::class, 'update']);
    Route::delete('/books/{book_id}', [EventProductController::class, 'destroy']);
});

Route::prefix('events')->group(function () {
    // Tạo event
    Route::post('/', [EventController::class, 'store'])->name('store');

    // Lấy danh sách tất cả event
    Route::get('/', [EventController::class, 'getall'])->name('events.getall');

    // Lấy chi tiết 1 event
    Route::get('/{event_id}', [EventController::class, 'show'])->name('events.show');

    // Cập nhật event
    Route::put('/{event_id}', [EventController::class, 'update'])->name('events.update');

    // Xoá event
    Route::delete('/{event_id}', [EventController::class, 'destroy'])->name('events.destroy');
});