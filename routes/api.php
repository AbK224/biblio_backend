<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivreController;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */
Route::get('/livres', [LivreController::class,'index']);
Route::post('/livres', [LivreController::class,'store']);
Route::get('/livres/{id}', [LivreController::class,'show']);
Route::put('/livres/{id}', [LivreController::class,'update']);
Route::delete('/livres/{id}', [LivreController::class,'destroy']);





