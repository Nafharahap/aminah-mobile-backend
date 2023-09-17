<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (Auth::user()->role == 'admin') {
                return $next($request);
            } else if (Auth::user()->role == 'borrower') {
                return redirect()->route('borrower.profile');
            } else if (Auth::user()->role == 'lender') {
                return redirect()->route('lender');
            }
        }

        return redirect('/');
    }
}
