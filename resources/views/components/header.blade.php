<header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-start h-16">
            <!-- Logo Text -->
            <div class="flex-shrink-0">
                <a href="/" class="flex items-center">
                    <span class="text-2xl font-bold text-indigo-600">Cookly</span>
                </a>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="ml-8 flex space-x-8">
                <a href="/" class="text-gray-900 font-medium {{ request()->is('/') ? 'border-b-2 border-indigo-500' : '' }}">
                    Beranda
                </a>
                <a href="/resep" class="text-gray-500 hover:text-gray-900 {{ request()->is('resep*') ? 'border-b-2 border-indigo-500' : '' }}">
                    Resep
                </a>
                <a href="/pengelolaan-daftar-belanja" class="text-gray-500 hover:text-gray-900 {{ request()->is('pengelolaan-daftar-belanja*') ? 'border-b-2 border-indigo-500' : '' }}">
                    Pengelolaan Daftar Belanja
                </a>
                {{-- <a href="/cari-resep" class="text-gray-500 hover:text-gray-900 {{ request()->is('cari-resep*') ? 'border-b-2 border-indigo-500' : '' }}">
                    Cari Resep
                </a> --}}
            </nav>
        </div>
    </div>
</header>