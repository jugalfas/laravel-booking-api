<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\TalentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

Route::middleware('auth.api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth.api:Talent'])->prefix('talent')->group(function () {
    Route::get('services', [TalentController::class, 'talentServices']);
    Route::post('add_service', [TalentController::class, 'addService']);
    Route::delete('remove_service', [TalentController::class, 'removeService']);
    Route::put('update_service', [TalentController::class, 'updateService']);
    
    Route::get('bookings', [TalentController::class, 'talentBookings']);
    Route::post('accept_booking/{bookingId}', [TalentController::class, 'acceptBooking']);
    Route::post('reject_booking/{bookingId}', [TalentController::class, 'rejectBooking']);
    Route::post('completed_booking/{bookingId}', [TalentController::class, 'completedBooking']);
    Route::post('cancelled_booking/{bookingId}', [TalentController::class, 'cancelledBooking']);
});

Route::middleware(['auth.api:Client'])->prefix('client')->group(function () {
    Route::get('get_talents', [ClientController::class, 'getTalents']);
    Route::get('get_talent_services', [ClientController::class, 'getTalentServices']);

    Route::post('book_talent', [ClientController::class, 'bookTalent']);
    Route::get('get_bookings', [ClientController::class, 'getBookings']);
    
    Route::post('completed_booking/{bookingId}', [ClientController::class, 'completedBooking']);
    Route::post('cancelled_booking/{bookingId}', [ClientController::class, 'cancelledBooking']);
});