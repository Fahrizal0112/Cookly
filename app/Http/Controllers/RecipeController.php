<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stichoza\GoogleTranslate\GoogleTranslate;

class RecipeController extends Controller
{
    private $apiKey;
    private $translator;

    public function __construct()
    {
        $this->apiKey = env('SPOONACULAR_API_KEY');
        $this->translator = new GoogleTranslate('id');
    }
    
    public function searchByIngredients(Request $request)
    {
        $ingredients = $request->input('ingredients'); 
        
        $response = Http::get('https://api.spoonacular.com/recipes/findByIngredients', [
            'apiKey' => $this->apiKey,
            'ingredients' => $ingredients,
            'number' => 6,
            'ranking' => 2, // 2 = maximize used ingredients
            'ignorePantry' => true
        ]);
        
        if ($response->successful()) {
            $recipes = $response->json();
            
            // Get detailed info for each recipe
            $detailedRecipes = [];
            foreach ($recipes as $recipe) {
                $details = Http::get("https://api.spoonacular.com/recipes/{$recipe['id']}/information", [
                    'apiKey' => $this->apiKey
                ])->json();
                
                $detailedRecipes[] = [
                    'id' => $recipe['id'],
                    'title' => $recipe['title'],
                    'image' => $recipe['image'],
                    'readyInMinutes' => $details['readyInMinutes'],
                    'difficulty' => $this->calculateDifficulty($details),
                    'matchPercentage' => round(($recipe['usedIngredientCount'] / ($recipe['usedIngredientCount'] + $recipe['missedIngredientCount'])) * 100)
                ];
            }
            
            return response()->json($detailedRecipes);
        }
        
        return response()->json(['error' => 'Failed to fetch recipes'], 500);
    }
    
    public function show($id)
    {
        try {
            $response = Http::get("https://api.spoonacular.com/recipes/{$id}/information", [
                'apiKey' => $this->apiKey
            ]);

            if ($response->successful()) {
                $recipe = $response->json();
                
                // Terjemahkan data
                $formattedRecipe = [
                    'title' => $this->translator->translate($recipe['title']),
                    'image' => $recipe['image'],
                    'readyInMinutes' => $recipe['readyInMinutes'],
                    'servings' => $recipe['servings'],
                    'ingredients' => $this->translateIngredients($recipe['extendedIngredients']),
                    'instructions' => $this->translateInstructions($recipe['analyzedInstructions'][0]['steps'] ?? []),
                    'summary' => $this->translator->translate($recipe['summary']),
                    'difficulty' => $this->calculateDifficulty($recipe)
                ];

                return view('recipes.show', ['recipe' => $formattedRecipe]);
            }

            return back()->with('error', 'Gagal mengambil detail resep');
        } catch (\Exception $e) {
            \Log::error('Error fetching recipe details: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengambil detail resep');
        }
    }

    private function translateIngredients($ingredients)
    {
        return array_map(function($ingredient) {
            return [
                'amount' => $ingredient['amount'],
                'unit' => $this->translateUnit($ingredient['unit']),
                'name' => $this->translator->translate($ingredient['name'])
            ];
        }, $ingredients);
    }

    private function translateInstructions($steps)
    {
        return array_map(function($step) {
            return [
                'number' => $step['number'],
                'step' => $this->translator->translate($step['step'])
            ];
        }, $steps);
    }

    private function translateUnit($unit)
    {
        $unitTranslations = [
            'cup' => 'gelas',
            'tablespoon' => 'sendok makan',
            'teaspoon' => 'sendok teh',
            'ounce' => 'ons',
            'pound' => 'pon',
            'gram' => 'gram',
            'kilogram' => 'kilogram',
            'ml' => 'ml',
            'liter' => 'liter',
            'piece' => 'buah',
            'slice' => 'iris',
            'whole' => 'utuh',
            'clove' => 'siung',
            // Tambahkan unit lain sesuai kebutuhan
        ];

        $lowercaseUnit = strtolower($unit);
        return $unitTranslations[$lowercaseUnit] ?? $unit;
    }

    private function calculateDifficulty($recipe)
    {
        $stepsCount = count($recipe['analyzedInstructions'][0]['steps'] ?? []);
        $ingredientsCount = count($recipe['extendedIngredients'] ?? []);
        
        if ($stepsCount <= 5 && $ingredientsCount <= 7) return 'Mudah';
        if ($stepsCount <= 10 && $ingredientsCount <= 12) return 'Sedang';
        return 'Sulit';
    }
}
