<x-app-layout>
    <x-header />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Header Section -->
            <div class="relative h-96">
                <img src="{{ $recipe['image'] }}" alt="{{ $recipe['title'] }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <h1 class="text-4xl font-bold text-white mb-4">{{ $recipe['title'] }}</h1>
                    <div class="flex items-center gap-6 text-white">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $recipe['readyInMinutes'] }} menit
                        </span>
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.318l-1.318 1.318A4.5 4.5 0 008.5 10.5"/>
                            </svg>
                            {{ $recipe['servings'] }} Porsi
                        </span>
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            {{ $recipe['difficulty'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="p-8">
                <!-- Summary -->
                <div class="mb-12">
                    <h2 class="text-2xl font-semibold mb-4">Tentang Resep Ini</h2>
                    <div class="prose prose-lg">{!! $recipe['summary'] !!}</div>
                </div>

                <!-- Ingredients -->
                <div class="mb-12">
                    <h2 class="text-2xl font-semibold mb-4">Bahan-bahan</h2>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($recipe['ingredients'] as $ingredient)
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $ingredient['amount'] }} {{ $ingredient['unit'] }} {{ $ingredient['name'] }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Instructions -->
                <div>
                    <h2 class="text-2xl font-semibold mb-4">Langkah-langkah</h2>
                    <div class="space-y-6">
                        @foreach($recipe['instructions'] as $step)
                        <div class="flex">
                            <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-500 font-semibold mr-4">
                                {{ $step['number'] }}
                            </div>
                            <p class="mt-1">{{ $step['step'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-footer />
</x-app-layout>