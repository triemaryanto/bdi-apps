<x-layouts.app title="Manajemen Korwil">
    @include('partials.navbar')
    <div class="px-4 py-6 max-w-lg mx-auto">
        <div class="flex items-center gap-2 mb-5">
            <a href="{{ route('rt.index') }}" class="text-gray-400 hover:text-gray-600">
                ← Daftar RT
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">Korwil RT</span>
        </div>

        <livewire:manajemen-korwil :rtId="$rtId" />
    </div>
</x-layouts.app>
