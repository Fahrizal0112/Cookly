<x-app-layout>
    <x-header />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold mb-8">Perencanaan Menu Mingguan yang Seimbang dan Hemat</h1>

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Jumlah Anggota Keluarga
                    </label>
                    <select id="family-size" class="w-full border-gray-300 rounded-lg shadow-sm">
                        <option value="1">1 Orang</option>
                        <option value="2">2 Orang</option>
                        <option value="3">3 Orang</option>
                        <option value="4">4 Orang</option>
                        <option value="5">5 Orang</option>
                        <option value="6">6 Orang</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Budget per Minggu
                    </label>
                    <select id="budget" class="w-full border-gray-300 rounded-lg shadow-sm">
                        <option value="300000">Rp 300.000</option>
                        <option value="500000">Rp 500.000</option>
                        <option value="750000">Rp 750.000</option>
                        <option value="1000000">Rp 1.000.000</option>
                        <option value="1500000">Rp 1.500.000</option>
                        <option value="2000000">Rp 2.000.000</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Masukkan alergi atau pantangan
                </label>
                <input type="text" 
                       id="allergies" 
                       class="w-full border-gray-300 rounded-lg shadow-sm" 
                       placeholder="Masukkan alergi atau pantangan...">
            </div>

            <div class="flex justify-end gap-4">
                <button id="generate-menu" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                    Buat Menu
                </button>
                <button id="save-menu" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 hidden">
                    {{-- Simpan Menu --}}
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="hidden text-center py-12">
            <svg class="animate-spin h-8 w-8 mx-auto text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-gray-600">Menghasilkan menu...</p>
        </div>

        <!-- Results Section -->
        <div id="results" class="hidden">
            <!-- Menu Mingguan -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4">Menu Minggu Ini</h2>
                <div id="weekly-menu" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Menu akan ditampilkan di sini -->
                </div>
            </div>

            <!-- Daftar Belanja -->
            <div>
                <h2 class="text-2xl font-bold mb-4">Daftar Belanja Mingguan</h2>
                <div id="grocery-list" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Daftar belanja akan ditampilkan di sini -->
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const generateButton = document.querySelector('#generate-menu');
            const saveButton = document.querySelector('#save-menu');
            const loadingState = document.querySelector('#loading-state');
            const resultsSection = document.querySelector('#results');
            const weeklyMenuContainer = document.querySelector('#weekly-menu');
            const groceryListContainer = document.querySelector('#grocery-list');

            generateButton.addEventListener('click', async function() {
                try {
                    loadingState.classList.remove('hidden');
                    resultsSection.classList.add('hidden');
                    saveButton.classList.add('hidden');

                    const response = await fetch('/meal-plan/generate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            family_size: document.querySelector('#family-size').value,
                            budget: document.querySelector('#budget').value,
                            allergies: document.querySelector('#allergies').value
                        })
                    });

                    const data = await response.json();
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // Render menu dan grocery list
                    weeklyMenuContainer.innerHTML = renderWeeklyMenu(data.menu);
                    groceryListContainer.innerHTML = renderGroceryList(data.groceryList);
                    
                    resultsSection.classList.remove('hidden');
                    saveButton.classList.remove('hidden');
                } catch (error) {
                    alert('Terjadi kesalahan: ' + error.message);
                } finally {
                    loadingState.classList.add('hidden');
                }
            });

            function renderWeeklyMenu(menu) {
                return Object.entries(menu).map(([day, meals]) => `
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="font-semibold text-lg mb-3">${sanitizeText(day)}</h3>
                        <div class="space-y-2">
                            <p><span class="font-medium">Sarapan:</span> ${sanitizeText(meals.sarapan)}</p>
                            <p><span class="font-medium">Makan Siang:</span> ${sanitizeText(meals.makan_siang)}</p>
                            <p><span class="font-medium">Makan Malam:</span> ${sanitizeText(meals.makan_malam)}</p>
                        </div>
                    </div>
                `).join('');
            }

            function renderGroceryList(groceryList) {
                return Object.entries(groceryList).map(([category, items]) => `
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="font-semibold text-lg mb-3">${sanitizeText(category)}</h3>
                        <ul class="space-y-2">
                            ${items.map(item => `
                                <li class="flex items-center">
                                    <span class="mr-2">•</span>
                                    ${sanitizeText(item.item)} (${sanitizeText(item.amount)})
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `).join('');
            }

            function sanitizeText(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            saveButton.addEventListener('click', async function() {
                try {
                    const response = await fetch('/meal-plan/save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    if (data.image_url) {
                        window.open(data.image_url, '_blank');
                    }
                    
                    alert(data.message);
                } catch (error) {
                    alert('Gagal menyimpan menu: ' + error.message);
                }
            });
        });
    </script>
    @endpush

    <x-footer />
</x-app-layout>
