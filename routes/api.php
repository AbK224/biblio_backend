<?php

use App\Http\Controllers\EmpruntController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivreController;
use App\Http\Controllers\UtilisateurController;

/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */
//CRUD Livres
Route::get('/livres', [LivreController::class,'index']);
Route::post('/livres', [LivreController::class,'store']);
Route::get('/livres/{id}', [LivreController::class,'show']);
Route::put('/livres/{id}', [LivreController::class,'update']);
Route::delete('/livres/{id}', [LivreController::class,'destroy']);

//CRUD utilisateur ou lecteur
Route::get('/users', [UtilisateurController::class,'index']);
Route::post('/users', [UtilisateurController::class,'store']);
Route::get('/users/{id}', [UtilisateurController::class,'show']);
Route::put('/users/{id}', [UtilisateurController::class,'update']);
Route::delete('/users/{id}', [UtilisateurController::class,'destroy']);

//CRUD emprunt
Route::get('/emprunt', [EmpruntController::class,'index']);
Route::post('/emprunt', [EmpruntController::class,'store']);
Route::get('/emprunt/{id}', [EmpruntController::class,'show']);
Route::put('/emprunt/{id}', [EmpruntController::class,'update']);
Route::delete('/emprunt/{id}', [EmpruntController::class,'destroy']);





