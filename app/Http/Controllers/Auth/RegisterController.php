<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.login',['mode' => 'register']);
    }

    public function register(Request $request)
    {
        $validated = $request->validateWithBag('register',[
            'name' => [
                'required',
                'string',
                'max:255','min:2'
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'terms' =>['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
          // not use for double hashing  'password' => Hash::make($validated
          // ['password']), REDUNDANT THE MODEL
          // retunr['password' =>'hashed']
          //will skip hashing a second time
          //only will do hashing one time 
            'password' => $validated['password'],
        ]);

        // Give every newly registered user the "user" role.
        $user->assignRole('user');

        // Log the user in using Laravel's session authentication.
        Auth::login($user);

        // Prevent session fixation.
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Registration successful.');
    }
}