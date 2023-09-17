<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BorrowerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LenderController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PengajuanController;
use App\Http\Controllers\Admin\MitraController;
use App\Http\Controllers\Admin\PendanaanController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\KeuanganController;
use App\Http\Controllers\TransactionController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

// require __DIR__ . '/auth.php';

// login
Route::get('/login', [LoginController::class, 'index']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// register borrower
Route::get('/mitra/daftar', [RegisterController::class, 'registerBorrower']);
Route::post('/mitra/daftar', [RegisterController::class, 'storeBorrower']);

// register lender
Route::get('/register', [RegisterController::class, 'registerLender']);
Route::post('/lender/daftar', [RegisterController::class, 'storeLender']);

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang-kami', [HomeController::class, 'about']);
Route::get('/cara-kerja', [HomeController::class, 'how_to_work']);
Route::get('/forgot-password', [LoginController::class, 'forgot_password']);
Route::get('/recovery-password', [LoginController::class, 'recovery_password']);

Route::get('/rincian-pendanaan/detail', [AdminController::class, 'detail_rincian_pendanaan']);
Route::get('/data-keuangan', [AdminController::class, 'data_keuangan']);
Route::get('/data-keuangan/detail', [AdminController::class, 'detail_keuangan']);

// admin
Route::middleware(['auth', 'isAdmin'])->group(function () {
    // dashboard
    Route::get('/administrator/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    // user
    Route::get('/administrator/user', [UserController::class, 'index'])->name('admin.user');
    Route::get('/administrator/user/tambah', [UserController::class, 'createAdmin'])->name('admin.user.tambah');
    Route::post('/administrator/tambah', [UserController::class, 'store'])->name('admin.tambah');
    // pengajuan
    Route::get('/administrator/pengajuan', [PengajuanController::class, 'index'])->name('admin.borrower');
    Route::get('/administrator/pengajuan/detail/{borrower}', [PengajuanController::class, 'detail'])->name('admin.borrower.detail');
    Route::post('/administrator/pengajuan/terima', [PengajuanController::class, 'approve'])->name('admin.borrower.approve');
    Route::post('/administrator/pengajuan/tolak', [PengajuanController::class, 'reject'])->name('admin.borrower.reject');
    // data mitra
    Route::get('/administrator/mitra', [MitraController::class, 'index'])->name('admin.partner');
    Route::get('/administrator/mitra/detail/{borrower}', [MitraController::class, 'detail'])->name('admin.partner.detail');
    Route::delete('/administrator/mitra/hapus', [MitraController::class, 'destroy'])->name('admin.partner.destroy');

    Route::get('/administrator/pendanaan', [PendanaanController::class, 'index'])->name('admin.funding');
    Route::get('/administrator/rincian-pendanaan/detail/{funding}', [PendanaanController::class, 'detail']);
    Route::get('/administrator/transaksi', [KeuanganController::class, 'index']);
    Route::get('/administrator/transaksi/detail/{trx_hash}', [KeuanganController::class, 'detail']);
    Route::post('/administrator/transaksi/terima', [KeuanganController::class, 'approve']);
    Route::post('/administrator/transaksi/tolak', [KeuanganController::class, 'reject']);
});

// mitra
Route::middleware(['auth', 'isBorrower'])->group(function () {
    Route::get('/mitra/profile', [BorrowerController::class, 'index'])->name('borrower.profile');
    Route::get('/mitra/profile/ajukan-pendanaan', [BorrowerController::class, 'pengajuan_pendanaan'])->name('borrower.profile.ajukan-pendanaan');
    Route::post('/mitra/pengajuan', [BorrowerController::class, 'storeBorrower']);
    Route::get('/mitra/saldo/tarik/invoice', [BorrowerController::class, 'withdrawal'])->name('borrower.withdrawal');
    Route::post('/mitra/saldo/tarik', [BorrowerController::class, 'storeWithdrawal'])->name('borrower.withdrawal.store');
    Route::get('/mitra/pendanaan/bayar/{funding}', [BorrowerController::class, 'returnFunding'])->name('borrower.return');
    Route::get('/mitra/pendanaan/bayar/detail/{trx_hash}', [BorrowerController::class, 'returnFundingDetail'])->name('borrower.return.detail');
    Route::post('/mitra/pendanaan/bayar', [BorrowerController::class, 'storeReturnFunding'])->name('borrower.return.store');
});

// lender
Route::middleware(['auth', 'isLender'])->group(function () {
    Route::get('/lender/home', [LenderController::class, 'index'])->name('lender');
    Route::get('/lender/profile', [LenderController::class, 'profile'])->name('lender.profile');
    Route::get('/lender/mitra', [LenderController::class, 'mitra'])->name('lender.mitra');
    Route::get('/lender/mitra/detail/{funding}', [LenderController::class, 'detailMitra'])->name('lender.mitra.detail');
    Route::get('/lender/profile/edit', [LenderController::class, 'editProfile'])->name('lender.profile.edit');
    Route::post('/lender/profile/update', [LenderController::class, 'updateProfile'])->name('lender.profile.update');
    //tarik saldo
    Route::get('/lender/saldo/tarik/invoice', [LenderController::class, 'withdrawal'])->name('lender.withdrawal');
    Route::post('/lender/saldo/tarik', [LenderController::class, 'storeWithdrawal'])->name('lender.withdrawal.store');


    Route::get('/lender/keranjang', [CartController::class, 'cartList'])->name('cart.list');
    Route::post('/cart', [CartController::class, 'addToCart'])->name('cart.store');
    Route::post('/update-cart', [CartController::class, 'updateCart'])->name('cart.update');
    Route::post('/remove', [CartController::class, 'removeCart'])->name('cart.remove');
    Route::post('/clear', [CartController::class, 'clearAllCart'])->name('cart.clear');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/lender/checkout/invoice', [CartController::class, 'invoice'])->name('cart.invoice');

    Route::get('/lender/dompet/isi', [TransactionController::class, 'recharge'])->name('lender.recharge');
    Route::post('/lender/dompet/isi', [TransactionController::class, 'storeRecharge'])->name('lender.recharge.store');
    Route::get('/lender/dompet/bayar', [TransactionController::class, 'pay'])->name('lender.recharge.pay');
    Route::get('/lender/dompet/bayar/detail/{trx_hash}', [TransactionController::class, 'payDetail'])->name('lender.recharge.detail');
    Route::post('/lender/dompet/bayar', [TransactionController::class, 'payStore'])->name('lender.recharge.pay.store');
});
