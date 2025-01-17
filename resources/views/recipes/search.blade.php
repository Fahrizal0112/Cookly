<x-app-layout>
    <x-header />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold mb-8">Cari Resep Masakan</h1>

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Masukkan Nama Masakan
                </label>
                <div class="flex gap-4">
                    <input type="text" 
                           id="recipe-query" 
                           class="flex-1 border-gray-300 rounded-lg shadow-sm" 
                           placeholder="Contoh: Nasi Goreng Spesial, Rendang, Soto Ayam...">
                    <button id="search-recipe" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                        Cari Resep
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="hidden text-center py-12">
            <svg class="animate-spin h-8 w-8 mx-auto text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-gray-600">Mencari resep...</p>
        </div>

        <!-- Results Section -->
        <div id="recipe-result" class="hidden">
            <!-- Recipe details will be rendered here -->
        </div>
    </main>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchButton = document.querySelector('#search-recipe');
            const loadingState = document.querySelector('#loading-state');
            const resultSection = document.querySelector('#recipe-result');

            searchButton.addEventListener('click', async function() {
                try {
                    const query = document.querySelector('#recipe-query').value.trim();
                    
                    if (!query) {
                        alert('Silakan masukkan nama masakan');
                        return;
                    }

                    loadingState.classList.remove('hidden');
                    resultSection.classList.add('hidden');

                    const response = await fetch('/cari-resep/search', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ query: query })
                    });

                    const data = await response.json();
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    resultSection.innerHTML = `
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-2xl font-bold mb-4">${data.nama}</h2>
                            <p class="text-gray-600 mb-6">${data.deskripsi}</p>
                            
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div>
                                    <span class="font-medium">Waktu Memasak:</span>
                                    <p>${data.waktu_memasak}</p>
                                </div>
                                <div>
                                    <span class="font-medium">Porsi:</span>
                                    <p>${data.porsi}</p>
                                </div>
                                <div>
                                    <span class="font-medium">Tingkat Kesulitan:</span>
                                    <p>${data.tingkat_kesulitan}</p>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h3 class="text-xl font-semibold mb-3">Bahan-bahan</h3>
                                <ul class="list-disc pl-5 space-y-2">
                                    ${data.bahan.map(b => `
                                        <li>${b.item}: ${b.jumlah}</li>
                                    `).join('')}
                                </ul>
                            </div>

                            <div class="mb-6">
                                <h3 class="text-xl font-semibold mb-3">Alat yang Dibutuhkan</h3>
                                <ul class="list-disc pl-5 space-y-2">
                                    ${data.alat.map(a => `<li>${a}</li>`).join('')}
                                </ul>
                            </div>

                            <div class="mb-6">
                                <h3 class="text-xl font-semibold mb-3">Cara Memasak</h3>
                                <ol class="list-decimal pl-5 space-y-4">
                                    ${data.langkah.map(l => `
                                        <li>${l.instruksi}</li>
                                    `).join('')}
                                </ol>
                            </div>

                            <div>
                                <h3 class="text-xl font-semibold mb-3">Tips</h3>
                                <ul class="list-disc pl-5 space-y-2">
                                    ${data.tips.map(t => `<li>${t}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `;
                    
                    resultSection.classList.remove('hidden');
                } catch (error) {
                    alert('Terjadi kesalahan: ' + error.message);
                } finally {
                    loadingState.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush

    <x-footer />
</x-app-layout>
