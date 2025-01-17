<x-app-layout>
    <x-header/>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Search Section -->
        <div class="bg-white rounded-lg shadow-sm p-8 mb-12">
            <h1 class="text-3xl font-bold mb-8">Temukan Resep dari Bahan yang Tersedia</h1>
            
            <!-- Search Input -->
            <div class="relative mb-4">
                <input 
                    type="text" 
                    id="ingredient-search"
                    class="w-full p-4 pl-12 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Masukkan bahan yang tersedia..."
                >
                <svg class="w-6 h-6 text-gray-400 absolute left-3 top-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Selected Ingredients -->
            <div id="selected-ingredients" class="flex flex-wrap gap-2 mb-6">
                <!-- Selected ingredients will be dynamically added here -->
            </div>

            <!-- Filter Buttons -->
            {{-- <div class="flex gap-3">
                <button class="px-4 py-2 rounded-full border border-gray-200 hover:bg-gray-50" id="mealTimeFilter">
                    Semua Waktu Makan
                </button>
                <button class="px-4 py-2 rounded-full border border-gray-200 hover:bg-gray-50" id="prepTimeFilter">
                    Waktu Persiapan
                </button>
                <button class="px-4 py-2 rounded-full border border-gray-200 hover:bg-gray-50" id="difficultyFilter">
                    Tingkat Kesulitan
                </button>
            </div> --}}
        </div>

        <!-- Recipe Cards Container -->
        <div id="recipe-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        </div>

        <div id="loading-state" class="hidden text-center py-12">
            <svg class="animate-spin h-8 w-8 mx-auto text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </main>

    <x-footer/>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('#ingredient-search');
            const selectedIngredientsContainer = document.querySelector('#selected-ingredients');
            const recipeContainer = document.querySelector('#recipe-container');
            const loadingState = document.querySelector('#loading-state');
            const selectedIngredients = new Set();
    
            // Daftar terjemahan bahan
            const ingredientTranslations = {
                // Sayuran
                "bayam": "spinach",
                "wortel": "carrot",
                "kentang": "potato",
                "bawang merah": "shallot",
                "bawang putih": "garlic",
                "cabai": "chili",
                "tomat": "tomato",
                "kangkung": "water spinach",
                "kol": "cabbage",
                "brokoli": "broccoli",
                
                // Protein
                "ayam": "chicken",
                "daging sapi": "beef",
                "ikan": "fish",
                "telur": "egg",
                "udang": "shrimp",
                "tahu": "tofu",
                "tempe": "tempeh",
                
                // Bumbu
                "garam": "salt",
                "merica": "pepper",
                "kunyit": "turmeric",
                "jahe": "ginger",
                "ketumbar": "coriander",
                "serai": "lemongrass",
                "kecap": "soy sauce",
                
                // Bahan Dasar
                "beras": "rice",
                "tepung": "flour",
                "minyak": "oil",
                "gula": "sugar",
                "susu": "milk",
                
                // Buah
                "pisang": "banana",
                "apel": "apple",
                "jeruk": "orange",
                "mangga": "mango",
                "nanas": "pineapple"
            };
    
            // Fungsi untuk menerjemahkan bahan
            function translateIngredient(indonesianName) {
                // Ubah ke lowercase untuk memudahkan pencarian
                const normalizedName = indonesianName.toLowerCase().trim();
                
                // Cari di daftar terjemahan
                const englishName = ingredientTranslations[normalizedName];
                
                if (englishName) {
                    return {
                        indonesian: indonesianName,
                        english: englishName
                    };
                }
                
                // Jika tidak ditemukan, kembalikan bahan asli
                return {
                    indonesian: indonesianName,
                    english: indonesianName // gunakan nama asli jika tidak ada terjemahan
                };
            }
    
            // Handle ingredient input dengan keydown dan submit
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const ingredient = this.value.trim();
                    
                    if (ingredient) {
                        const translation = translateIngredient(ingredient);
                        addIngredient(translation);
                        this.value = ''; // Clear input
                    }
                }
            });
    
            function addIngredient(translation) {
                if (!selectedIngredients.has(translation.english)) {
                    selectedIngredients.add(translation.english);
                    renderSelectedIngredients();
                    updateSearch();
                }
            }
    
            function renderSelectedIngredients() {
                selectedIngredientsContainer.innerHTML = '';
                
                selectedIngredients.forEach(englishName => {
                    // Cari nama Indonesia dari daftar terjemahan
                    const indonesianName = Object.entries(ingredientTranslations)
                        .find(([indo, eng]) => eng === englishName)?.[0] || englishName;
                    
                    const tag = document.createElement('span');
                    tag.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100';
                    tag.innerHTML = `
                        ${indonesianName} (${englishName})
                        <button class="ml-1 text-gray-500 hover:text-gray-700" 
                                onclick="removeIngredient('${englishName}')">×</button>
                    `;
                    selectedIngredientsContainer.appendChild(tag);
                });
            }
    
            window.removeIngredient = function(englishName) {
                selectedIngredients.delete(englishName);
                renderSelectedIngredients();
                updateSearch();
            };
    
            function updateSearch() {
                if (selectedIngredients.size === 0) {
                    recipeContainer.innerHTML = '<p class="text-center col-span-3 text-gray-500">Masukkan bahan untuk mencari resep</p>';
                    return;
                }
    
                loadingState.classList.remove('hidden');
                recipeContainer.classList.add('hidden');
    
                const ingredients = Array.from(selectedIngredients).join(',');
    
                fetch('/search-recipes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ ingredients })
                })
                .then(response => response.json())
                .then(recipes => {
                    loadingState.classList.add('hidden');
                    recipeContainer.classList.remove('hidden');
                    updateRecipeCards(recipes);
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingState.classList.add('hidden');
                    recipeContainer.classList.remove('hidden');
                    recipeContainer.innerHTML = '<p class="text-center col-span-3 text-red-500">Terjadi kesalahan saat mencari resep</p>';
                });
            }

            function updateRecipeCards(recipes) {
                if (recipes.length === 0) {
                    recipeContainer.innerHTML = '<p class="text-center col-span-3 text-gray-500">Tidak ada resep yang ditemukan</p>';
                    return;
                }

                recipeContainer.innerHTML = recipes.map(recipe => `
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <img src="${recipe.image}" alt="${recipe.title}" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="text-xl font-semibold mb-2">${recipe.title}</h3>
                            <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    ${recipe.readyInMinutes} menit
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    ${recipe.difficulty}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-4">${recipe.matchPercentage}% kecocokan bahan</div>
                            <div class="flex gap-2">
                                <button onclick="window.location.href='/recipe/${recipe.id}'" class="flex-1 bg-black text-white py-2 px-4 rounded-lg hover:bg-gray-800">
                                    Lihat Resep
                                </button>
                                <button class="p-2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        });
    </script>
    @endpush
</x-app-layout>