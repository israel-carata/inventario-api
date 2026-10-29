<?php

use App\Http\Controllers\ProductoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Estado de la API y conexión a la base de datos
Route::get('/estado', function () {
    try {
        DB::connection()->getPdo();
        $db = DB::connection()->getDriverName();
    } catch (\Throwable $e) {
        $db = 'sin conexión';
    }

    return response()->json([
        'api' => 'Inventario API',
        'estado' => 'ok',
        'base_de_datos' => $db,
        'hora' => now()->toIso8601String(),
    ]);
});

// CRUD completo: index, store, show, update, destroy
Route::apiResource('productos', ProductoController::class);
