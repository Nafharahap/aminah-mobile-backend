<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lender;
use App\Models\Funding;
use App\Models\Borrower;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\FundingLender;
use App\Models\LenderStatusType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class LenderController extends Controller
{
  public function index(Request $request)
  {
    $fundings = Funding::where('is_finished', '0')->active()->inRandomOrder()->limit(2)->get();
    foreach ($fundings as $funding) {
      $totalUnitTerjual = $funding->fundinglenders->sum('unit_amount');
      $danaTerkumpul = $totalUnitTerjual * env('HARGA_UNIT', 100000);
      $dana_terkumpul = $danaTerkumpul;
      $funding->dana_terkumpul = $dana_terkumpul;
      $funding->dana_terkumpul_persen = ($dana_terkumpul != 0) ? $dana_terkumpul / $funding->accepted_fund * 100 : 0;
    }

    $data = array(
      'title' => "Aminah | Home",
      'active' => 'home',
      'mitra' => $fundings,
    );

    if ($request->is('api/*')) {
      if ($fundings) {
        return $this->makeJson($data);
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    return view('pages.lender.home', $data);
  }
  public function mitra(Request $request)
  {
    $fundings = Funding::with(['borrower'])->where('is_finished', '0')->active()->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->paginate(10);
    foreach ($fundings as $funding) {
      $totalUnitTerjual = $funding->fundinglenders->sum('unit_amount');
      $dana_terkumpul = $totalUnitTerjual * env('HARGA_UNIT', 100000);
      $funding->dana_terkumpul = $dana_terkumpul;
      $funding->dana_terkumpul_persen = ($dana_terkumpul != 0) ? $dana_terkumpul / $funding->accepted_fund * 100 : 0;
    }

    $data = array(
      'title' => "Aminah | Mitra",
      'active' => 'mitra',
      'mitra' => $fundings,
    );

    if ($request->is('api/*')) {
      if ($data) {
        return $this->makeJson($data);
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    $borrowers = '';
    if ($request->ajax()) {
      foreach ($fundings as $funding) {
        $borrowers .= View::make("components.card.borrower")->with('mitra', $funding)->render();
      }
      return $borrowers;
    }

    return view('pages.lender.mitra', $data);
  }

  public function detailMitra(Funding $funding, Request $request)
  {
    // dd($funding);
    // dd($borrower->fundings[0]->accepted_fund);
    if ($funding) {
      $totalUnitTerjual = $funding->fundinglenders->sum('unit_amount');
      $dana_terkumpul = $totalUnitTerjual * env('HARGA_UNIT', 100000);
      $funding->dana_terkumpul = $dana_terkumpul;
      $funding->dana_terkumpul_persen = ($dana_terkumpul != 0) ? $dana_terkumpul / $funding->accepted_fund * 100 : 0;
      $funding->sisa_unit = ($funding->accepted_fund - $dana_terkumpul) / env('HARGA_UNIT', 100000);
      $funding->borrower = $funding->borrower;
      $funding->borrower->user = $funding->borrower->user;

      foreach ($funding->fundinglenders as $fundingLender) {
        $fundingLender->lender = $fundingLender->lender;
      }
    }

    $data = array(
      "title" => "Aminah | Detail Mitra",
      'active' => 'mitra',
      'funding' => $funding,
    );

    if ($request->is('api/*')) {
      if ($data) {
        return $this->makeJson($data);
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    return view('pages.lender.mitra.detail', $data);
  }

  public function profile(Request $request)
  {
    $userID = Auth::user()->id;
    $user = User::with('lender')->find($userID);
    $user->sumAmount = $user->sumAmount();
    $user->checkProfile = $user->checkProfile;
    $user->lender = $user->lender;
    $userFundings = FundingLender::where('lender_id', $userID)->latest()->get();

    $data = array(
      'title' => "Aminah | Profile",
      'active' => 'profile',
      'user' => $user,
      'userFundings' => $userFundings,
    );

    if ($request->is('api/*')) {
      if ($data) {
        return $this->makeJson($data);
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    return view('pages.lender.profile.index', $data);
  }

  public function editProfile()
  {
    $data = array(
      'title' => "Aminah | Form Lengkapi Profile",
      'active' => 'profile',
    );

    return view('pages.lender.profile.edit', $data);
  }

  public function updateProfile(Request $request)
  {
    $this->validate($request, [
      'nama'                  => 'required',
      'jenisKelamin'          => 'required',
      'tempatLahir'           => 'required',
      'tanggalLahir'          => 'required',
      'noHp'                  => 'required',
      'nik'                   => 'required',
      'alamat'                => 'required',
      'pemilikRekeningName'   => 'required',
      'nomorRekening'         => 'required',
      'bankName'              => 'required',
      'file-diri'             => 'required|file|mimes:jpg,jpeg,png,pdf',
      'file-ktp'              => 'required|file|mimes:jpg,jpeg,png,pdf',
      'approvedCheck'         => 'required',
    ]);

    $status = LenderStatusType::where('name', 'Verified')->first();

    $current = date('Ymdhis');
    $rand = rand(1, 100);
    $fileName = $current . $rand;

    $fileDiri = $request->file('file-diri');
    $fileKTP = $request->file('file-ktp');

    $fileNameDiri = $fileName . '_diri.' . $fileDiri->getClientOriginalExtension();
    $fileNameKTP = $fileName . '_ktp.' . $fileKTP->getClientOriginalExtension();
    $fileDiri->move('profile', $fileNameDiri);
    $fileKTP->move('profile', $fileNameKTP);

    $lender = new lender();
    $lender->name = $request->input('nama');
    $lender->email = Auth::user()->email;
    $lender->jenis_kelamin = $request->input('jenisKelamin');
    $lender->tempat_lahir = $request->input('tempatLahir');
    $lender->tanggal_lahir = $request->input('tanggalLahir');
    $lender->status = isset($status) ? $status->name : 'Verified';
    $lender->hp_number = $request->input('noHp');
    $lender->nik = $request->input('nik');
    $lender->address = $request->input('alamat');
    $lender->account_name = $request->input('pemilikRekeningName');
    $lender->account_number = $request->input('nomorRekening');
    $lender->bank_name = $request->input('bankName');
    $lender->lender_image = isset($fileNameDiri) ? $fileNameDiri : null;
    $lender->ktp_image = isset($fileNameKTP) ? $fileNameKTP : null;
    $saving = $lender->save();

    if ($request->is('api/*')) {
      if ($saving) {
        return $this->makeJson('Berhasil mengubah profil');
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    if ($saving) {
      return redirect()
        ->to('/lender/profile')
        ->with([
          'success' => 'Berhasil mengubah profil'
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

  //invoice view
  public function withdrawal(Request $request)
  {
    if (Auth::user()->sumAmount() == 0) {
      if ($request->is('api/*')) {
        return $this->makeJson('Saldo anda kosong', false, 400);
      }

      return redirect('/lender/profile')
        ->with([
          'error' => 'Saldo anda kosong'
        ]);
    }

    $lenderSumAmmount = Auth::user()->sumAmount();
    $lender = Auth::user()->lender;

    $data = array(
      'title' => "Aminah | Invoice Penarikan dana",
      'active' => 'profile',
      'lenderSumAmmount' => $lenderSumAmmount,
      'lender' => $lender
    );

    if ($request->is('api/*')) {
      if ($data) {
        return $this->makeJson($data);
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    return view('pages.lender.profile.invoice_tarik', $data);
  }

  //klik tarik saldo di view invoice
  public function storeWithdrawal(Request $request)
  {
    $user_amount = Auth::user()->sumAmount();
    $user_id = Auth::user()->id;

    $this->validate($request, [
      'bankName' => 'required',
      'pemilikRekeningName' => 'required',
      'nomorRekening' => 'required',
      'jumlahSaldo' => "required|numeric|max:$user_amount",
    ]);

    $bankName = $request->input('bankName');
    $accountName = $request->input('pemilikRekeningName');
    $accountNumber = $request->input('nomorRekening');
    $withdrawalAmount = $request->input('jumlahSaldo');

    $transaction = new Transaction();
    $transaction->trx_hash = md5($user_id . now());
    $transaction->transaction_type = '3';
    $transaction->transaction_date = now();
    $transaction->transaction_datetime = now();
    $transaction->status = 'pending';
    $transaction->user_id = $user_id;
    $transaction->lender_id = $user_id;
    $transaction->transaction_amount = $withdrawalAmount;
    $transaction->recepient_name = $accountName;
    $transaction->recepient_account_number = $accountNumber;
    $transaction->recepient_bank_name = $bankName;
    $saving = $transaction->save();

    if ($request->is('api/*')) {
      if ($saving) {
        return $this->makeJson('Berhasil mengajukan permintaan penarikan saldo');
      } else {
        return $this->makeJson('Maaf gagal, coba lagi nanti', false, 400);
      }
    }

    if ($saving) {
      return redirect('/lender/profile')
        ->with([
          'success' => 'Berhasil mengajukan permintaan penarikan saldo'
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
