<?php

use App\Http\Controllers\Api\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BorrowerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LenderController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [LoginController::class, 'index']);

// Register
Route::post('/mitra/daftar', [RegisterController::class, 'storeBorrower']);
Route::post('/lender/daftar', [RegisterController::class, 'storeLender']);

// Mitra Public API
Route::get('/lender/mitra', [LenderController::class, 'mitra'])->name('lender.mitra');
Route::get('/lender/mitra/detail/{funding}', [LenderController::class, 'detailMitra'])->name('lender.mitra.detail');

Route::middleware('auth:api')->group(function () {
  Route::prefix('mitra')->group(function () {
    Route::get('/profile', [BorrowerController::class, 'index'])->name('borrower.profile');
    Route::get('/profile/ajukan-pendanaan', [BorrowerController::class, 'pengajuan_pendanaan'])->name('borrower.profile.ajukan-pendanaan');
    Route::post('/pengajuan', [BorrowerController::class, 'storeBorrower']);
    Route::get('/saldo/tarik/invoice', [BorrowerController::class, 'withdrawal'])->name('borrower.withdrawal');
    Route::post('/saldo/tarik', [BorrowerController::class, 'storeWithdrawal'])->name('borrower.withdrawal.store');
    Route::get('/pendanaan/bayar/{funding}', [BorrowerController::class, 'returnFunding'])->name('borrower.return');
    Route::get('/pendanaan/bayar/lender/{borrower_id}', [BorrowerController::class, 'returnFundingLender'])->name('borrower.return.lender');
    Route::get('/pendanaan/bayar/detail/{trx_hash}', [BorrowerController::class, 'returnFundingDetail'])->name('borrower.return.detail');
    Route::post('/pendanaan/bayar', [BorrowerController::class, 'storeReturnFunding'])->name('borrower.return.store');
  });

  Route::prefix('lender')->group(function () {
    Route::get('/home', [LenderController::class, 'index'])->name('lender');
    Route::get('/profile', [LenderController::class, 'profile'])->name('lender.profile');
    Route::get('/profile/edit', [LenderController::class, 'editProfile'])->name('lender.profile.edit');
    Route::post('/profile/update', [LenderController::class, 'updateProfile'])->name('lender.profile.update');
    //tarik saldo
    Route::get('/saldo/tarik/invoice', [LenderController::class, 'withdrawal'])->name('lender.withdrawal');
    Route::post('/saldo/tarik', [LenderController::class, 'storeWithdrawal'])->name('lender.withdrawal.store');

    Route::get('/keranjang', [CartController::class, 'cartList'])->name('cart.list');
    Route::post('/cart', [CartController::class, 'addToCart'])->name('cart.store');
    Route::post('/update-cart', [CartController::class, 'updateCart'])->name('cart.update');
    Route::post('/remove', [CartController::class, 'removeCart'])->name('cart.remove');
    Route::post('/clear', [CartController::class, 'clearAllCart'])->name('cart.clear');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/checkout/invoice', [CartController::class, 'invoice'])->name('cart.invoice');
    Route::post('/checkout-api', [CartController::class, 'checkOutApi'])->name('cart.checkout.api');
    Route::get('/transaksi', [TransactionController::class, 'transactionList'])->name('lender.transactionList');
    Route::get('/transaksi/{trx_hash}', [TransactionController::class, 'transactionDetail'])->name('lender.transactionDetail');

    Route::get('/dompet/isi', [TransactionController::class, 'recharge'])->name('lender.recharge');
    Route::post('/dompet/isi', [TransactionController::class, 'storeRecharge'])->name('lender.recharge.store');
    Route::get('/dompet/bayar', [TransactionController::class, 'pay'])->name('lender.recharge.pay');
    Route::get('/dompet/bayar/detail/{trx_hash}', [TransactionController::class, 'payDetail'])->name('lender.recharge.detail');
    Route::post('/dompet/bayar', [TransactionController::class, 'payStore'])->name('lender.recharge.pay.store');
  });
});
