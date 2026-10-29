<?php

use Illuminate\Support\Facades\Route;

// Panel para probar la API desde el navegador
Route::get('/', function () {
    return view('panel');
});
