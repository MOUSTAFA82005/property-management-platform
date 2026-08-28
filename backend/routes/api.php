<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner\BuildingController as OwnerBuildingController;
use App\Http\Controllers\Owner\ContractController as OwnerContractController;
use App\Http\Controllers\Owner\CustomerController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\PaymentController as OwnerPaymentController;
use App\Http\Controllers\Owner\PropertyController as OwnerPropertyController;
use App\Http\Controllers\Owner\PurchaseRequestController as OwnerPurchaseRequestController;
use App\Http\Controllers\Owner\UnitController as OwnerUnitController;
use App\Http\Controllers\Customer\ContractController as CustomerContractController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\PropertyController as CustomerPropertyController;
use App\Http\Controllers\Customer\PurchaseRequestController as CustomerPurchaseRequestController;
use App\Http\Controllers\Customer\UnitController as CustomerUnitController;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------
// Public — Auth
// -------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);
});

// -------------------------------------------------------
// Public — property catalog
// -------------------------------------------------------
// Browsing published stock needs no account. These deliberately sit outside
// the auth:sanctum group so an anonymous visitor can reach them, and the
// controllers restrict every query to published, active properties.
Route::get('/properties', [CustomerPropertyController::class, 'index']);
Route::get('/properties/{property}', [CustomerPropertyController::class, 'show']);
Route::get('/properties/{property}/units', [CustomerUnitController::class, 'index']);
Route::get('/units/{unit}', [CustomerUnitController::class, 'show']);

// -------------------------------------------------------
// Protected — shared auth
// -------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', LogoutController::class);
    Route::get('/auth/me', MeController::class);

    // ---------------------------------------------------
    // Owner routes
    // ---------------------------------------------------
    // role:owner keeps customers out of the owner portal entirely. Ownership
    // of individual records is still enforced per-controller/per-policy.
    Route::prefix('owner')->middleware('role:owner')->group(function () {

        Route::get('/dashboard', OwnerDashboardController::class);

        Route::apiResource('/properties', OwnerPropertyController::class);
        Route::post('/properties/{property}/publish', [OwnerPropertyController::class, 'publish']);
        Route::post('/properties/{property}/unpublish', [OwnerPropertyController::class, 'unpublish']);

        Route::apiResource('/buildings', OwnerBuildingController::class);
        Route::apiResource('/units', OwnerUnitController::class);
        Route::apiResource('/contracts', OwnerContractController::class);
        Route::apiResource('/payments', OwnerPaymentController::class);

        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);

        Route::get('/purchase-requests', [OwnerPurchaseRequestController::class, 'index']);
        Route::get('/purchase-requests/{purchaseRequest}', [OwnerPurchaseRequestController::class, 'show']);
        Route::post('/purchase-requests/{purchaseRequest}/approve', [OwnerPurchaseRequestController::class, 'approve']);
        Route::post('/purchase-requests/{purchaseRequest}/reject', [OwnerPurchaseRequestController::class, 'reject']);
    });

    // ---------------------------------------------------
    // Customer routes
    // ---------------------------------------------------
    // apiResource would generate the parameter {purchase_request}, which does not
    // match the $purchaseRequest argument on the controller — implicit model
    // binding silently skips when the names differ. Pin the parameter name.
    Route::apiResource('/purchase-requests', CustomerPurchaseRequestController::class)
        ->only(['index', 'store', 'show', 'destroy'])
        ->parameters(['purchase-requests' => 'purchaseRequest']);

    Route::get('/contracts', [CustomerContractController::class, 'index']);
    Route::get('/contracts/{contract}', [CustomerContractController::class, 'show']);

    Route::get('/payments', [CustomerPaymentController::class, 'index']);
    Route::get('/payments/{payment}', [CustomerPaymentController::class, 'show']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // ---------------------------------------------------
    // Notifications (both roles)
    // ---------------------------------------------------
    // Not under /owner: a notification belongs to a user, not to a portal,
    // and every query is scoped to the token holder inside the controller.
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
});