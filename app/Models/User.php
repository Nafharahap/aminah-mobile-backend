<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Mockery\Undefined;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  public function lender()
  {
    return $this->belongsTo(Lender::class, 'email', 'email');
  }

  public function borrower()
  {
    return $this->hasMany(Borrower::class, 'email', 'email');
  }

  public function waitingBorrower()
  {
    return $this->hasMany(Borrower::class, 'email', 'email')
      ->where('status', 'Pending');
  }

  public function latestBorrower()
  {
    return $this->hasOne(Borrower::class, 'email', 'email')->latest();
  }

  public function checkProfile()
  {
    return $this->hasOne(Lender::class, 'email', 'email')
      ->where('name', '!=', null)
      ->where('jenis_kelamin', '!=', null)
      ->where('tempat_lahir', '!=', null)
      ->where('tanggal_lahir', '!=', null)
      ->where('hp_number', '!=', null)
      ->where('nik', '!=', null)
      ->where('address', '!=', null);
  }

  public function lenderRecharge()
  {
    return $this->hasMany(Transaction::class)
      ->where('transaction_type', ['1'])
      ->where('status', ['waiting'])
      ->orderBy('created_at', 'desc');
  }

  public function lenderTransactions($typeFilter = null)
  {
    if (count($typeFilter) == 0) {
      $typeFilter = ['1', '3', '6'];
    }

    return $this->hasMany(Transaction::class)->with('funding',  'funding.borrower')
      ->whereIn('transaction_type', $typeFilter)
      ->whereIn('status', ['success', 'pending'])
      ->orderBy('created_at', 'desc');
  }

  public function checkWaiting()
  {
    return $this->hasMany(Transaction::class)
      ->whereIn('transaction_type', ['1'])
      ->whereIn('status', ['waiting']);
  }

  public function checkIncome()
  {
    return $this->hasMany(Transaction::class)
      ->whereIn('status', ['accepted', 'success'])
      ->whereIn('transaction_type', ['1', '2']);
  }

  public function checkExpense()
  {
    return $this->hasMany(Transaction::class)
      ->whereIn('status', ['accepted', 'success', 'requested'])
      ->whereIn('transaction_type', ['3', '6']);
  }

  public function sumAmount()
  {
    return $this->checkIncome->sum('transaction_amount') - $this->checkExpense->sum('transaction_amount');
  }

  public function borrowerIncome()
  {
    return $this->hasMany(Transaction::class, 'borrower_user_id')
      ->whereHas('fundingLender', function ($query) {
        $query->whereIn('status', ['success', 'on progress']);
      });
  }

  public function borrowerExpense()
  {
    return $this->hasMany(Transaction::class, 'borrower_user_id')
      ->whereIn('status', ['waiting', 'accepted', 'success'])
      ->whereIn('transaction_type', ['4']);
  }

  public function borrowerAmount()
  {
    return $this->borrowerIncome->sum('transaction_amount') - $this->borrowerExpense->sum('transaction_amount');
  }

  public function transactions()
  {
    return $this->hasMany(Transaction::class);
  }

  public function scopeAdmin($query)
  {
    $query->where('role', 'admin');
  }

  public function scopeLender($query)
  {
    $query->where('role', 'lender');
  }

  public function scopeBorrower($query)
  {
    $query->where('role', 'borrower');
  }

  public function rollApiKey()
  {
    do {
      $this->api_token = Str::random(60);
    } while ($this->where('api_token', $this->api_token)->exists());

    $this->save();
  }
}
