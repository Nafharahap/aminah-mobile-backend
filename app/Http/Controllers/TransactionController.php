<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class TransactionController extends Controller
{
  public function index()
  {
    $data = array(
      'title' => 'Aminah | Detail Transaksi',
      'active' => 'profile',
      'page' => 'Detail Transaksi',
    );
    return view('pages.transaction.invoice', $data);
  }

  public function pay(Request $request)
  {
    $lenderRecharge = Auth::user()->lenderRecharge;

    $data = array(
      'title' => 'Aminah | Pembayaran',
      'active' => 'profile',
      'page' => 'Pembayaran',
      'lenderRecharge' => $lenderRecharge
    );

    if ($request->is('api/*')) {
      if ($data) {
        return $this->makeJson($data);
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    return view('pages.lender.dompet.pay', $data);
  }

  public function payDetail(Request $request, $hash)
  {
    $transaction = Transaction::where('trx_hash', $hash)->first();
    if (!$transaction) {
      return redirect()->to('/lender/dompet/bayar')->withErrors('Data tidak ditemukan');
    }

    $bankAccounts = BankAccount::all();

    $data = array(
      'title' => 'Aminah | Detail Pembayaran',
      'active' => 'profile',
      'page' => 'Detail Pembayaran',
      'transaction' => $transaction,
      'bankAccounts' => $bankAccounts,
    );

    if ($request->is('api/*')) {
      if ($data) {
        return $this->makeJson($data);
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    return view('pages.lender.dompet.pay_detail', $data);
  }

  public function payStore(Request $request)
  {
    $this->validate($request, [
      'file_trx' => 'required|file|mimes:jpg,jpeg,png,pdf',
    ]);

    $trx_hash = $request->input('trx_hash');
    $fileTrx = $request->file('file_trx');

    $transaction = Transaction::where('trx_hash', $trx_hash)->first();

    if (!$transaction) {
      if ($request->is('api/*')) {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }

      return redirect()->back()->withErrors('Gagal')->withInput();
    }

    $current = date('Ymdhis');
    $rand = rand(1, 100);
    $fileName = $current . $rand;
    $fileName = $fileName . '_bukti.' . $fileTrx->getClientOriginalExtension();
    $fileTrx->move('pembayaran', $fileName);

    $transaction->status = 'pending';
    $transaction->file_image = $fileName;
    $saving = $transaction->save();

    if ($request->is('api/*')) {
      if ($saving) {
        return $this->makeJson('Berhasil upload bukti pembayaran');
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    if ($saving) {
      return redirect()
        ->to('/lender/dompet/bayar')
        ->with([
          'success' => 'Berhasil upload bukti pembayaran'
        ]);
    } else {
      return redirect()
        ->back()
        ->withInput()
        ->with([
          'error' => 'Maaf gagal, coba lagi nanti'
        ]);
    }
  }

  public function transactionList(Request $request)
  {
    $lenderTransactions = Auth::user()->lenderTransactions()->paginate(10);

    $data = array(
      'title' => 'Aminah | Pembayaran',
      'active' => 'profile',
      'page' => 'Pembayaran',
      'lenderTransactions' => $lenderTransactions
    );

    if ($request->is('api/*')) {
      if ($data) {
        return $this->makeJson($data);
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }
  }

  public function recharge()
  {
    $data = array(
      'title' => 'Aminah | Isi Saldo',
      'active' => 'profile',
      'page' => 'Isi Saldo',
    );
    return view('pages.lender.dompet.recharge', $data);
  }

  public function storeRecharge(Request $request)
  {
    $user_id = Auth::user()->id;
    $nominal = $request->input('product');

    $transaction = new Transaction();
    $transaction->trx_hash = md5($user_id . now());
    $transaction->transaction_type = '1';
    $transaction->status = 'waiting';
    $transaction->user_id = $user_id;
    $transaction->transaction_date = now();
    $transaction->transaction_datetime = now();
    $transaction->transaction_amount = $nominal;
    $saving = $transaction->save();

    if ($request->is('api/*')) {
      if ($saving) {
        return $this->makeJson('Segera lakukan pembayaran');
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    if ($saving) {
      return redirect()
        ->to('/lender/profile')
        ->with([
          'success' => 'Segera lakukan pembayaran'
        ]);
    } else {
      return redirect()
        ->back()
        ->withInput()
        ->with([
          'error' => 'Maaf gagal, coba lagi nanti'
        ]);
    }
  }
}
