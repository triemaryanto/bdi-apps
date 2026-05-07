<x-layouts.app title="Manajemen RT">
    @include('partials.navbar')
    <div class="px-4 py-6 max-w-lg mx-auto">
        <div class="flex items-center gap-2 mb-5">
            <a href="{{ route('dashboard.superadmin') }}" class="text-gray-400 hover:text-gray-600">
                ← Dashboard
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">Manajemen RT</span>
        </div>

        <livewire:manajemen-rt />
    </div>
</x-layouts.app>
