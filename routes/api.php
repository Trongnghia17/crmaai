<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerDebtHistoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\ProductStorageController;
use App\Http\Controllers\Api\ReceiptPaymentController;
use App\Http\Controllers\Api\ReceiptTypeController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SuppliersController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [AuthController::class, 'store']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/confirm-otp', [AuthController::class, 'confirmOTP']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
});


Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/user', function () {
        return auth()->user();
    });

    Route::put('/change-info', [AuthController::class, 'updateStoreName']);

    Route::get('/dash-board', [DashboardController::class, 'index']);

    Route::group(['prefix' => 'customers'], function () {
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/', [CustomerController::class, 'index']);
        Route::get('/{id}', [CustomerController::class, 'detail']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'delete']);
        Route::post('/export-customers', [CustomerController::class, 'exportCustomer']);
    });

    Route::group(['prefix' => 'debt'], function () {
        Route::get('/{id}', [CustomerDebtHistoryController::class, 'show']);
        Route::post('/{id}', [CustomerDebtHistoryController::class, 'store']);
        Route::get('/repay/{id}', [CustomerDebtHistoryController::class, 'repayDebt']);
    });

    Route::group(['prefix' => 'suppliers'], function () {
        Route::post('/', [SuppliersController::class, 'store']);
        Route::get('/', [SuppliersController::class, 'index']);
        Route::put('/{id}', [SuppliersController::class, 'update']);
        Route::delete('/{id}', [SuppliersController::class, 'delete']);
        Route::post('/export-suppliers', [SuppliersController::class, 'exportSupplier']);
    });
    Route::get('/v2/products', [ProductsController::class, 'indexV2']);
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductsController::class, 'index']);
        Route::post('/', [ProductsController::class, 'store']);
        Route::get('/{id}', [ProductsController::class, 'show']);
        Route::put('/{id}', [ProductsController::class, 'update']);
        Route::delete('/{id}', [ProductsController::class, 'destroy']);
        Route::post('/import', [ProductsController::class, 'import']);
        Route::post('/export-products', [ProductsController::class, 'export']);
    });
    Route::group(['prefix' => 'orders'], function () {
        Route::post('/', [OrderController::class, 'create']);
        Route::get('/{id}', [OrderController::class, 'detail']);
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/cancel/{id}', [OrderController::class, 'cancel']);
        Route::delete('/delete/{id}', [OrderController::class, 'delete']);
        Route::post('/payment/{id}', [OrderController::class, 'payment']);
        Route::put('/{id}', [OrderController::class, 'update']);
        Route::post('export-orders', [OrderController::class, 'exportOrder']);
        Route::post('export-customer-order/{id}', [OrderController::class, 'exportCustomerOrder']);
        Route::post('export-supplier-order/{id}', [OrderController::class, 'exportSupplierOrder']);
    });

    Route::group(['prefix' => 'print'], function () {
        Route::post('/{id}', [OrderController::class, 'print']);
    });


    Route::group(['prefix' => 'report'], function () {
        Route::get('/aggregate', [ReportController::class, 'aggregate']);
        Route::get('/receipt-payment', [ReportController::class, 'receiptPayment']);
        Route::get('/storage', [ReportController::class, 'storage']);
        Route::get('/customer', [ReportController::class, 'customer']);
        Route::get('/supplier', [ReportController::class, 'supplier']);
        Route::get('/profit-loss', [ReportController::class, 'profitAndLoss']);
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/get-storage', [UserController::class, 'getStorage']);
    });
    Route::group(['prefix'=>'product-storage'], function(){
        Route::get('history-storage', [ProductStorageController::class, 'getHistoryStorage']);
    });

    Route::group(['prefix' => 'inventories'], function () {
       Route::get('/', [InventoryController::class, 'index']);
       Route::post('/', [InventoryController::class, 'create']);
       Route::get('/{id}', [InventoryController::class, 'detail']);
       Route::delete('/{id}', [InventoryController::class, 'delete']);
    });

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'create']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'delete']);
        Route::get('/{id}', [CategoryController::class, 'detail']);
    });

    Route::group(['prefix' => 'employee'], function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'create']);
        Route::put('/{id}', [EmployeeController::class, 'update']);
        Route::delete('/{id}', [EmployeeController::class, 'delete']);
    });
    Route::group(['prefix' => 'receipt-type'], function () {
        Route::get('/', [ReceiptTypeController::class, 'index']);
        Route::post('/', [ReceiptTypeController::class, 'create']);
        Route::put('/{id}', [ReceiptTypeController::class, 'update']);
        Route::get('/{id}', [ReceiptTypeController::class, 'detail']);
        Route::delete('/{id}', [ReceiptTypeController::class, 'delete']);
    });
    Route::group(['prefix' => 'receipt-payment'], function () {
        Route::post('/receipt', [ReceiptPaymentController::class, 'createReceipt']);
        Route::put('/receipt/{id}', [ReceiptPaymentController::class, 'updateReceipt']);
        Route::get('/receipt', [ReceiptPaymentController::class, 'receipt']);
        Route::get('/{id}', [ReceiptPaymentController::class, 'detail']);
        Route::post('/payment', [ReceiptPaymentController::class, 'createPayment']);
        Route::put('/payment/{id}', [ReceiptPaymentController::class, 'updatePayment']);
        Route::get('/payment', [ReceiptPaymentController::class, 'payment']);
        Route::delete('/{id}', [ReceiptPaymentController::class, 'delete']);
    });
});
