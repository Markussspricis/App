<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return view('welcome');
});

Route::get('/password-update', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('welcome');
});
