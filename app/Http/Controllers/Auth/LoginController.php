<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login',['mode' => 'login']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validateWithBag('login',[
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

             if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'those credentials not match our records'],'login')
                ->onlyInput('email');
        }

            // Prevent session fixation after authentication.
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Remove the current session.
        $request->session()->invalidate();
        // Generate a new CSRF token.
        $request->session()->regenerateToken();
//redirect('/login') is less sustainable hard
        return redirect()->route('login')
            ->with('success', 'You have been logged out.');
    }
}