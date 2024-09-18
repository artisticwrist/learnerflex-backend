<?php

use App\Http\Controllers\Auths\LoginController;
use App\Http\Controllers\Auths\LogoutController;
use App\Http\Controllers\Auths\RegisterController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Flutterwave\WithdrawalController;
use App\Http\Controllers\Flutterwave\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auths\PasswordResetLinkController;
use App\Http\Controllers\Auths\NewPasswordController;
use App\Http\Controllers\PasswordReset\PasswordResetController;
use App\Http\Controllers\PasswordReset\NewPasswordReset;
use App\Http\Controllers\Payment\PaystackController;
use App\Http\Controllers\Payment\MarketplacePaymentController;


Route::post('/get-balance', [UserController::class, 'getBalance']);


Route::post('/request-withdrawal', [UserController::class, 'requestWithdrawal']);

//one time payment
Route::post('/payment/initialize-marketplace', [MarketplacePaymentController::class, 'make_payment']);


Route::post('/payment/callback-marketplace', [MarketplacePaymentController::class, 'payment_callback']);

// Route to initialize payment
Route::post('/payment/initialize', [PaystackController::class, 'initializePayment']);

// Route to handle payment callback
Route::post('/payment/callback', [PaystackController::class, 'handlePaymentCallback']);

Route::get('/user/{id}/product/{reffer_id}', [ProductController::class, 'getProduct']);

Route::post('product/add-product', [ProductController::class, 'addProduct']);

Route::get('product/view-product/{vendor_id}/', [ProductController::class, 'viewProductsByVendor']);

Route::get('product/view-product/{vendor_id}/{product_id}', [ProductController::class, 'viewProductByVendor']);

Route::delete('/product/delete/{id}', [ProductController::class, 'deleteProduct']);


Route::post('password/reset-link', [PasswordResetController::class, 'sendPasswordResetLink']);
Route::post('password/new-password', [NewPasswordReset::class, 'resetPassword']);


Route::post('user/request-vendor', [VendorController::class, 'sendVendorRequest']);
Route::post('user/accept-vendor-request', [VendorController::class, 'store']);

Route::get('/vendor/{id}/total-sales', [VendorController::class, 'getVendorTotalSaleAmount']);


Route::get('vendor/{id}/transactions', [VendorController::class, 'getVendorSales']);

Route::get('vendor/{id}/students', [VendorController::class, 'getStudentEmailsAndNames']);

Route::get('/vendor/{id}/balance', [VendorController::class, 'vendorEarnings']);

Route::get('/user/{id}/balance', [UserController::class, 'affiliateEarnings']);


Route::post('/cart/add', [ProductController::class, 'addToCart'])->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'store']);
    Route::post('/login', [LoginController::class, 'attemptUser']);
    Route::post('/logout', [LogoutController::class, 'logout']);
});

Route::post('/vendor/login', [VendorController::class, 'login']);


Route::get('/users', [UserController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{user}/transactions', [UserController::class, 'transactions']);
    Route::patch('/users/{user}/currency', [UserController::class, 'transactions']);
    Route::post('/user/update/image', [UserController::class, 'handleUserImage']);
    Route::post('/user/update/profile', [UserController::class, 'handleUserProfile']);
    Route::patch('/users/update/{user}/vendor/status', [UserController::class, 'handleUserVendorStatus']);
    Route::post('/user/request/vendor', [UserController::class, 'handleVendorRequest']);
    Route::get('/users/{user}/vendor', [VendorController::class, 'index']);
    Route::post('/vendor/create', [VendorController::class, 'store']);
    Route::delete('/vendors/{vendor}/delete', [VendorController::class, 'delete']);
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->name('password.update');

    // unlock market
    Route::get('/user/unlock/market', [ProductController::class, 'unlockMarketAccess']);

    // products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/status/{status}', [ProductController::class, 'getApprovedProducts']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/product/digital/create', [ProductController::class, 'createDigitalProduct']);
    Route::post('/product/other/create', [ProductController::class, 'createOtherProduct']);
    Route::patch('/products/{product}/update', [ProductController::class, 'edit']);
    Route::delete('/products/{product}/delete', [ProductController::class, 'destroy']);

    // withdrawals
    Route::get('/withdrawals', [WithdrawalController::class, 'index']);
    Route::get('/withdrawals/amount', [WithdrawalController::class, 'userWithdrawSum']);

    // dashboard home endpoints
    Route::get('/todays-affiliate-sales', [UserController::class, 'handleTodaysAffSales']);
    Route::get('/total-affiliate-sales', [UserController::class, 'handleTotalAffiliateSales']);
    Route::get('/available-affiliate-earnings', function () {
        return response()->json(['amount' => 20000]);
    });

    // check bank account route and update user account
    Route::post('/get-account-name', [PaymentController::class, 'handleCheckAccount']);
});
