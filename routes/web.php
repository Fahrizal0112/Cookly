<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/search-recipes', [RecipeController::class, 'searchByIngredients']);
Route::get('/recipe/{id}', [RecipeController::class, 'show'])->name('recipe.show');