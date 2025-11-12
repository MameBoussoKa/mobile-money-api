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
use App\Http\Controllers\Auth\EmailConfirmationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route as RouteFacade;

RouteFacade::post('/register', [RegisterController::class, 'register']);
RouteFacade::post('/login', [LoginController::class, 'login']);
RouteFacade::post('/confirm-email', [EmailConfirmationController::class, 'confirm']);

// Protected client routes
RouteFacade::middleware('auth:sanctum')->group(function () {
    RouteFacade::get('/client/balance', [ClientController::class, 'getBalance']);
    RouteFacade::post('/client/pay', [ClientController::class, 'pay']);
    RouteFacade::post('/client/transfer', [ClientController::class, 'transfer']);
    RouteFacade::get('/client/transactions', [ClientController::class, 'getTransactions']);
});

