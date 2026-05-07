<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public
Route::get('/', fn() => view('welcome'));
Route::get('/offline', fn() => view('offline'));
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::get('/pending', fn() => view('auth.pending'))->name('pending');

// Firebase Auth
Route::post('/auth/firebase', [AuthController::class, 'firebaseLogin']);
Route::post('/fcm/token',     [AuthController::class, 'saveFcmToken'])->middleware('firebase.auth');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard per role (protected)
Route::middleware('firebase.auth')->group(function () {
    Route::get('/dashboard/warga',      fn() => view('dashboard.warga'))->middleware('firebase.auth:warga,pengurus,admin,superadmin');
    Route::get('/dashboard/pengurus',   fn() => view('dashboard.pengurus'))->middleware('firebase.auth:pengurus,admin,superadmin');
    Route::get('/dashboard/admin',      fn() => view('dashboard.admin'))->middleware('firebase.auth:admin,superadmin');
    Route::get('/dashboard/superadmin', fn() => view('dashboard.superadmin'))->name('dashboard.superadmin')->middleware('firebase.auth:superadmin');
});

// Manajemen RT & Korwil (superadmin only)
Route::middleware(['firebase.auth', 'firebase.auth:superadmin'])->group(function () {
    Route::get('/rt',              fn() => view('rt.index'))->name('rt.index');
    Route::get('/rt/{rtId}/korwil', fn(string $rtId) => view('rt.korwil', compact('rtId')))->name('rt.korwil');
});

// Protected routes (authenticated users)
Route::middleware('firebase.auth')->group(function () {
    Route::get('/user',        fn() => view('user.index'))->middleware('firebase.auth:admin,superadmin');
    Route::get('/warga',       fn() => view('warga.index'))->middleware('firebase.auth:pengurus,admin,superadmin');
    Route::get('/iuran',       fn() => view('iuran.index'))->middleware('firebase.auth:pengurus,admin,superadmin,warga');
    Route::get('/pengumuman',  fn() => view('pengumuman.index'));
    Route::get('/surat',       fn() => view('surat.index'));
    Route::get('/keamanan',    fn() => view('keamanan.index'));
});
