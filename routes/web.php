<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginAuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\AirTxnController;
use App\Http\Controllers\MpesaTxnController;
use App\Http\Controllers\BuyAirtimeController;
use App\Http\Controllers\TxnController;
use App\Http\Controllers\LoyaltyControler;
use App\Http\Controllers\RetryController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//REGISTER
Route::get('terms', [LoginAuthController::class, 'terms'])->name('terms');
Route::get('/', [Dashboard::class, 'dashboard'])->name('home');
Route::get('login', [LoginAuthController::class, 'index'])->name('login');
Route::post('custom-login', [LoginAuthController::class, 'customLogin'])->name('login.custom');
//Route::get('register', [LoginAuthController::class, 'registration'])->name('register-user');
//Route::post('custom-registration', [LoginAuthController::class, 'customRegistration'])->name('register.custom');
Route::get('signout', [LoginAuthController::class, 'signOut'])->name('signout');

//PASSWORD RESET
Route::get('forgot-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');

//M-PESA  TRANSACTIONS
Route::get('mpesa', [MpesaTxnController::class, 'index'])->name('mpesa');
Route::get('transactions', [TxnController::class, 'show'])->name('txn');
Route::get('txn', [TxnController::class, 'txn'])->name('all');

// CUSTOMER REWARD
Route::get('loyalty', [LoyaltyControler::class, 'index'])->name('loyalty');
Route::get('reward', [LoyaltyControler::class, 'bonus'])->name('reward');

//AIRTIME TRANSACTIONS
Route::get('airtime', [AirTxnController::class, 'index'])->name('airtime_txn');
Route::get('purchase', [BuyAirtimeController::class, 'index'])->name('buy_airtime');
Route::post('purchase', [BuyAirtimeController::class, 'purchase'])->name('buy');
Route::post('stk', [BuyAirtimeController::class, 'stkpush'])->name('stk');

//S U B M A N A G E
Route::get('request', [SubscriptionController::class, 'show'])->name('submanage.sms');
Route::post('submanage', [SubscriptionController::class, 'submanage'])->name('submit.request');
Route::get('que', [SubscriptionController::class, 'outbox'])->name('submanage.outbox');

//RETRY
Route::get('delay', [RetryController::class, 'api'])->name('api_retry');
Route::post('api', [RetryController::class, 'api_retry'])->name('api');
Route::get('namba', [RetryController::class, 'namba'])->name('namba_retry');
Route::post('num', [RetryController::class, 'namba_retry'])->name('namba');
Route::get('mfailure', [RetryController::class, 'mfailure'])->name('m_retry');
Route::post('failure', [RetryController::class, 'm_retry'])->name('mfailure');
