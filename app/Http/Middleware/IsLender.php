<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsLender
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (Auth::user()->role == 'admin') {
                return redirect()->route('admin.borrower');
            } else if (Auth::user()->role == 'borrower') {
                return redirect()->route('borrower.profile');
            } else if (Auth::user()->role == 'lender') {
                return $next($request);
            }
        }

        return redirect('/');
    }
}
