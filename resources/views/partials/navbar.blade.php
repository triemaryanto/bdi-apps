<nav class="bg-blue-700 text-white px-4 py-3 flex items-center justify-between sticky top-0 z-10 shadow">
    <div class="flex items-center gap-2">
        <span class="text-xl">🏘️</span>
        <span class="font-semibold text-sm">BDI Apps</span>
    </div>
    <div class="flex items-center gap-3">
        @if(session('user_photo'))
            <img src="{{ session('user_photo') }}" class="w-8 h-8 rounded-full" alt="foto">
        @endif
        <a href="/logout" class="text-xs text-blue-200 hover:text-white">Keluar</a>
    </div>
</nav>
