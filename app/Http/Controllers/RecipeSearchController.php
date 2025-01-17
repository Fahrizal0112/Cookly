<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecipeSearchController extends Controller
{
    private $geminiApiKey;
    
    public function __construct()
    {
        $this->geminiApiKey = env('GEMINI_API_KEY');
    }

    public function index()
    {
        return view('recipes.search');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:3'
        ]);

        try {
            $prompt = $this->formatPrompt($validated['query']);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=' . $this->geminiApiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to get response from Gemini API');
            }

            $result = $this->parseGeminiResponse($response->json());
            
            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Error searching recipe: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mencari resep. Silakan coba lagi.'
            ], 500);
        }
    }

    private function formatPrompt($query)
    {
        return "Kamu adalah koki profesional. Berikan resep dan cara memasak {$query} dalam format JSON yang valid seperti contoh berikut, tanpa teks tambahan atau format lain:

{
  \"nama\": \"nama masakan\",
  \"deskripsi\": \"deskripsi singkat masakan\",
  \"waktu_memasak\": \"estimasi waktu memasak\",
  \"porsi\": \"jumlah porsi\",
  \"tingkat_kesulitan\": \"mudah/sedang/sulit\",
  \"bahan\": [
    {
      \"item\": \"nama bahan\",
      \"jumlah\": \"jumlah yang dibutuhkan\"
    }
  ],
  \"alat\": [
    \"alat yang dibutuhkan\"
  ],
  \"langkah\": [
    {
      \"step\": 1,
      \"instruksi\": \"langkah memasak\"
    }
  ],
  \"tips\": [
    \"tips memasak\"
  ]
}";
    }

    private function parseGeminiResponse($response)
    {
        try {
            $text = $response['candidates'][0]['content']['parts'][0]['text'];
            
            // Bersihkan teks dari karakter yang tidak diinginkan
            $text = trim($text);
            
            // Cari JSON dalam teks
            if (preg_match('/(\{.*\})/s', $text, $matches)) {
                $text = $matches[1];
            }
            
            $data = json_decode($text, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Gagal memproses response: ' . $e->getMessage());
        }
    }
}
