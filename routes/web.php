<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! session()->has('mbg_admin')) {
        return redirect()->route('login');
    }

    return view('welcome');
})->name('dashboard');

Route::get('/login', function () {
    if (session()->has('mbg_admin')) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if ($credentials['username'] !== 'admin' || $credentials['password'] !== 'admin123') {
        return back()
            ->withErrors(['username' => 'Username atau password tidak sesuai.'])
            ->onlyInput('username');
    }

    $request->session()->regenerate();
    $request->session()->put('mbg_admin', [
        'username' => 'admin',
        'name' => 'Risa Hayatun Nupus',
        'role' => 'Admin Program MBG',
    ]);

    Cookie::queue(cookie(
        name: 'mbg_admin',
        value: 'admin',
        minutes: 120,
        httpOnly: true,
        sameSite: 'lax'
    ));

    return redirect()->intended(route('dashboard'));
})->name('login.store');

Route::post('/logout', function (Request $request) {
    $request->session()->forget('mbg_admin');
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    Cookie::queue(Cookie::forget('mbg_admin'));

    return redirect()->route('login');
})->name('logout');
