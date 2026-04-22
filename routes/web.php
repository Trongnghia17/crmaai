<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CompanyController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/', [AttendanceController::class, 'index']);
Route::get('/dashboard', [AttendanceController::class, 'dashboard'])->name('dashboard');
Route::post('/check-in', [AttendanceController::class, 'checkIn']);
Route::post('/check-out', [AttendanceController::class, 'checkOut']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/history', [AttendanceController::class, 'history']);

Route::get('/attendance_detail', [AttendanceController::class, 'attendance_detail']);
Route::get('/attendances/export', [AttendanceController::class, 'export']);

Route::post('/users/store', [AuthController::class, 'store_user']);
Route::post('/users/update/{id}', [AuthController::class, 'update_user']);
Route::get('/users/delete/{id}', [AuthController::class, 'delete_user']);



Route::post('/companies/store', [CompanyController::class, 'store']);
Route::post('/companies/update/{id}', [CompanyController::class, 'update']);
Route::get('/companies/delete/{id}', [CompanyController::class, 'delete']);

