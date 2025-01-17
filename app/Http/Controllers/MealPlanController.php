<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class MealPlanController extends Controller
{
    private $geminiApiKey;
    
    public function __construct()
    {
        $this->geminiApiKey = env('GEMINI_API_KEY');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'family_size' => 'required|integer|min:1',
            'budget' => 'required|integer|min:300000',
            'allergies' => 'nullable|string'
        ]);

        try {
            $prompt = $this->formatPrompt(
                $validated['family_size'],
                $validated['budget'],
                $validated['allergies']
            );

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
                \Log::error('Gemini API Error:', ['response' => $response->json()]);
                throw new \Exception('Failed to get response from Gemini API');
            }

            $responseData = $response->json();
            
            if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Invalid response format from Gemini API');
            }

            $result = $this->parseGeminiResponse($responseData);
            
            // Simpan data menu ke session
            session(['last_generated_menu' => $result]);
            
            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Error generating menu: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal menghasilkan menu. Silakan coba lagi.'
            ], 500);
        }
    }

    private function formatPrompt($familySize, $budget, $allergies)
    {
        return "Kamu adalah ahli gizi dan koki profesional. Buatkan rekomendasi menu makanan Indonesia sehat dan hemat untuk {$familySize} orang dengan budget Rp {$budget} per minggu" . 
               ($allergies ? " dengan mempertimbangkan alergi/pantangan: {$allergies}." : ".") .
               " Format response HARUS dalam bentuk JSON yang valid seperti contoh berikut, tanpa teks tambahan atau format lain:

{
  \"menu\": {
    \"Senin\": {
      \"sarapan\": \"menu sarapan\",
      \"makan_siang\": \"menu makan siang\",
      \"makan_malam\": \"menu makan malam\"
    },
    \"Selasa\": {
      \"sarapan\": \"menu sarapan\",
      \"makan_siang\": \"menu makan siang\",
      \"makan_malam\": \"menu makan malam\"
    }
  },
  \"groceryList\": {
    \"Sayuran\": [
      {\"item\": \"nama sayur\", \"amount\": \"jumlah\"}
    ],
    \"Protein\": [
      {\"item\": \"jenis protein\", \"amount\": \"jumlah\"}
    ],
    \"Bumbu & Lainnya\": [
      {\"item\": \"nama bumbu\", \"amount\": \"jumlah\"}
    ]
  }
}";
    }

    private function parseGeminiResponse($response)
    {
        try {
            // Ambil teks dari response Gemini
            $text = $response['candidates'][0]['content']['parts'][0]['text'];
            
            // Log response mentah untuk debugging
            \Log::info('Raw Gemini response:', ['text' => $text]);
            
            // Bersihkan teks dari karakter yang tidak diinginkan
            $text = trim($text);
            
            // Cari JSON dalam teks (mengambil teks di antara kurung kurawal pertama dan terakhir)
            if (preg_match('/(\{.*\})/s', $text, $matches)) {
                $text = $matches[1];
            }
            
            // Log teks yang sudah dibersihkan
            \Log::info('Cleaned text:', ['text' => $text]);
            
            // Parse JSON
            $data = json_decode($text, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('JSON decode error:', [
                    'error' => json_last_error_msg(),
                    'text' => $text
                ]);
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            // Pastikan struktur data sesuai yang diharapkan
            if (!isset($data['menu']) || !isset($data['groceryList'])) {
                throw new \Exception('Invalid response structure');
            }

            return $data;
        } catch (\Exception $e) {
            \Log::error('Error parsing Gemini response:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Gagal memproses response: ' . $e->getMessage());
        }
    }

    public function save(Request $request)
    {
        try {
            // Ambil data menu dari session
            $menuData = session('last_generated_menu');
            
            if (!$menuData || !isset($menuData['menu']) || !isset($menuData['groceryList'])) {
                throw new \Exception('Data menu tidak ditemukan. Silakan generate menu terlebih dahulu.');
            }

            // Buat image manager
            $manager = new ImageManager(new Driver());
            
            // Buat canvas dengan ukuran yang cukup besar
            $img = $manager->canvas(1200, 1600, '#ffffff');
            
            // Tambahkan judul
            $img->text('Menu Makanan Mingguan', 600, 50, function($font) {
                $font->file(public_path('fonts/Poppins-Bold.ttf'));
                $font->size(32);
                $font->color('#000000');
                $font->align('center');
            });

            // Render menu mingguan
            $y = 120;
            foreach ($menuData['menu'] as $day => $meals) {
                $img->text($day, 50, $y, function($font) {
                    $font->file(public_path('fonts/Poppins-SemiBold.ttf'));
                    $font->size(24);
                    $font->color('#000000');
                });
                
                $y += 40;
                $img->text("Sarapan: {$meals['sarapan']}", 70, $y, function($font) {
                    $font->file(public_path('fonts/Poppins-Regular.ttf'));
                    $font->size(16);
                    $font->color('#000000');
                });
                
                $y += 30;
                $img->text("Makan Siang: {$meals['makan_siang']}", 70, $y, function($font) {
                    $font->file(public_path('fonts/Poppins-Regular.ttf'));
                    $font->size(16);
                    $font->color('#000000');
                });
                
                $y += 30;
                $img->text("Makan Malam: {$meals['makan_malam']}", 70, $y, function($font) {
                    $font->file(public_path('fonts/Poppins-Regular.ttf'));
                    $font->size(16);
                    $font->color('#000000');
                });
                
                $y += 50;
            }

            // Tambahkan daftar belanja
            $img->text('Daftar Belanja', 600, $y, function($font) {
                $font->file(public_path('fonts/Poppins-Bold.ttf'));
                $font->size(24);
                $font->color('#000000');
                $font->align('center');
            });

            $y += 50;
            foreach ($menuData['groceryList'] as $category => $items) {
                $img->text($category, 50, $y, function($font) {
                    $font->file(public_path('fonts/Poppins-SemiBold.ttf'));
                    $font->size(20);
                    $font->color('#000000');
                });
                
                $y += 30;
                foreach ($items as $item) {
                    $img->text("• {$item['item']} ({$item['amount']})", 70, $y, function($font) {
                        $font->file(public_path('fonts/Poppins-Regular.ttf'));
                        $font->size(16);
                        $font->color('#000000');
                    });
                    $y += 25;
                }
                $y += 20;
            }

            // Generate nama file unik
            $filename = 'menu-' . now()->format('Y-m-d-His') . '.png';
            
            // Simpan gambar
            Storage::disk('public')->put('menus/' . $filename, $img->encode('png'));

            // Return URL gambar
            $imageUrl = Storage::url('menus/' . $filename);
            
            return response()->json([
                'message' => 'Menu berhasil disimpan sebagai gambar',
                'image_url' => $imageUrl
            ]);

        } catch (\Exception $e) {
            \Log::error('Error saving menu as image: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal menyimpan menu sebagai gambar: ' . $e->getMessage()
            ], 500);
        }
    }
}
