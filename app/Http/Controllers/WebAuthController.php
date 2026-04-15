<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WebAuthController extends Controller
{
    public function showAuth(?string $mode = null)
    {
        if ($mode !== null && ! in_array($mode, ['signin', 'signup'], true)) {
            return redirect('/auth/signin');
        }

        return view('auth');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Invalid credentials')->withInput($request->only('email'));
        }

        $token = Str::random(80);
        $user->update(['api_token' => $token]);
        $user->refresh();

        $request->session()->put('api_token', $token);
        $request->session()->put('user', ['name' => $user->name, 'email' => $user->email]);

        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        return redirect('/home');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $token = Str::random(80);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'api_token' => $token,
        ]);

        $request->session()->put('api_token', $token);
        $request->session()->put('user', ['name' => $user->name, 'email' => $user->email]);

        return redirect('/update-profile');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['api_token', 'user']);

        return redirect('/');
    }
}
