<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
         ]);
         
         
         if(User::where('email', $request->get('email'))->exists()){
            $user = User::where('email', $request->get('email'))->first();
            $auth = Hash::check($request->get('password'), $user->password);
            if($user && $auth){
         
               $user->rollApiKey();
         
               return response(array(
                  'user' => $user,
                  'message' => 'Authorization Successful!',
               ));
            }
         }
         return response(array(
            'message' => 'Unauthorized, check your credentials.',
         ), 401);
    }

    public function logout(Request $request) {

    }
}
