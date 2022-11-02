<?php

use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::get('/logout', function () {
    request()->session()->invalidate();
});

// Route::view('/', 'home');
Route::view('/{any}', 'app')->where("any", ".*");
