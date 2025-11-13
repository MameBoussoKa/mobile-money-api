<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Register endpoint for clients
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SmsConfirmationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompteController;
use Illuminate\Support\Facades\Route as RouteFacade;

RouteFacade::post('/register', [RegisterController::class, 'register']);
RouteFacade::post('/login', [LoginController::class, 'login']);
RouteFacade::post('/confirm-sms', [SmsConfirmationController::class, 'confirm']);

// Protected client routes
RouteFacade::middleware('auth:sanctum')->group(function () {
    RouteFacade::post('/logout', [LoginController::class, 'logout']);
    RouteFacade::get('/compte/{id}/solde', [CompteController::class, 'getSolde']);
    RouteFacade::post('/compte/{id}/pay', [CompteController::class, 'pay']);
    RouteFacade::post('/compte/{id}/transfer', [CompteController::class, 'transfer']);
    RouteFacade::get('/compte/{id}/transactions', [CompteController::class, 'getTransactions']);
});

