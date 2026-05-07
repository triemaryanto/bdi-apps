<x-layouts.app title="Iuran & Keuangan">
    @include('partials.navbar')
    <div class="px-4 py-6 max-w-lg mx-auto">
        <div class="flex items-center gap-2 mb-5">
            <a href="javascript:history.back()" class="text-gray-400 hover:text-gray-600">← Kembali</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">Iuran & Keuangan</span>
        </div>
        <livewire:manajemen-iuran />
    </div>
</x-layouts.app>
