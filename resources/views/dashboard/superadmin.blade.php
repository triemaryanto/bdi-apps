<x-layouts.app title="Dashboard Superadmin">
    @include('partials.navbar')
    <div id="offline-banner" class="hidden bg-yellow-400 text-yellow-900 text-xs text-center py-2 font-medium">
        📵 Mode Offline — Menampilkan data terakhir yang tersimpan
    </div>
    <div class="px-4 py-6 max-w-lg mx-auto">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <p class="text-xs text-yellow-700 font-medium uppercase tracking-wide">Role</p>
            <p class="text-lg font-bold text-yellow-800">⚡ Superadmin</p>
        </div>
        <h2 class="text-base font-semibold text-gray-800 mb-4">Selamat datang, {{ session('user_name') }}!</h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('rt.index') }}" class="bg-white rounded-xl p-4 shadow-sm border text-center hover:bg-yellow-50 transition">
                <div class="text-2xl mb-1">🏘️</div>
                <p class="text-xs text-gray-600 font-medium">Kelola RT</p>
            </a>
            <a href="/user" class="bg-white rounded-xl p-4 shadow-sm border text-center hover:bg-yellow-50 transition">
                <div class="text-2xl mb-1">👤</div>
                <p class="text-xs text-gray-600 font-medium">Kelola User</p>
            </a>
            <a href="/warga" class="bg-white rounded-xl p-4 shadow-sm border text-center hover:bg-yellow-50 transition">
                <div class="text-2xl mb-1">👥</div>
                <p class="text-xs text-gray-600 font-medium">Data Warga</p>
            </a>
            <a href="/iuran" class="bg-white rounded-xl p-4 shadow-sm border text-center hover:bg-yellow-50 transition">
                <div class="text-2xl mb-1">💰</div>
                <p class="text-xs text-gray-600 font-medium">Keuangan</p>
            </a>
            <a href="/pengumuman" class="bg-white rounded-xl p-4 shadow-sm border text-center hover:bg-yellow-50 transition">
                <div class="text-2xl mb-1">📢</div>
                <p class="text-xs text-gray-600 font-medium">Pengumuman</p>
            </a>
            <a href="/surat" class="bg-white rounded-xl p-4 shadow-sm border text-center hover:bg-yellow-50 transition">
                <div class="text-2xl mb-1">📋</div>
                <p class="text-xs text-gray-600 font-medium">Surat</p>
            </a>
            <a href="/keamanan" class="bg-white rounded-xl p-4 shadow-sm border text-center hover:bg-yellow-50 transition col-span-2">
                <div class="text-2xl mb-1">🚨</div>
                <p class="text-xs text-gray-600 font-medium">Keamanan & Ronda</p>
            </a>
        </div>
    </div>
    <script>
        window.addEventListener('online',  () => document.getElementById('offline-banner').classList.add('hidden'));
        window.addEventListener('offline', () => document.getElementById('offline-banner').classList.remove('hidden'));
        if (!navigator.onLine) document.getElementById('offline-banner').classList.remove('hidden');
    </script>
</x-layouts.app>
