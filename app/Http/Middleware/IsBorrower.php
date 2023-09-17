<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsBorrower
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (Auth::user()->role == 'admin') {
                return redirect()->route('admin.borrower');
            } else if (Auth::user()->role == 'borrower') {
                return $next($request);
            } else if (Auth::user()->role == 'lender') {
                return redirect()->route('lender');
            }
        }

        return redirect('/');
    }
}
