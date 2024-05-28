<?php

use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 发送短信验证码
Route::post('/send-code', [SmsController::class, 'sendVerificationCode']);
// 验证短信验证码
Route::post('/verify-code', [SmsController::class, 'verifyCode']);
