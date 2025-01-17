<?php

use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipeSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/search-recipes', [RecipeController::class, 'searchByIngredients']);
Route::get('/recipe/{id}', [RecipeController::class, 'show'])->name('recipe.show');
Route::post('/meal-plan/generate', [MealPlanController::class, 'generate']);
Route::get('/resep', function () {
    return view('recipes.index');
})->name('recipes.index');
Route::get('/cari-resep', [RecipeSearchController::class, 'index'])->name('recipe.search');
Route::post('/cari-resep/search', [RecipeSearchController::class, 'search'])->name('recipe.search.post');