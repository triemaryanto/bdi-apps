<div>
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-2">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-semibold text-gray-800">Daftar RT</h2>
        <button wire:click="openCreate"
            class="bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg hover:bg-blue-700">
            + Tambah RT
        </button>
    </div>

    {{-- Form --}}
    @if ($showForm)
        <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">
                {{ $editId ? 'Edit RT' : 'Tambah RT Baru' }}
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Nama RT *</label>
                    <input wire:model="nama" type="text" placeholder="cth: RT 01"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Alamat</label>
                    <input wire:model="alamat" type="text" placeholder="cth: Jl. Mawar No. 1"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div class="flex gap-2 pt-1">
                    <button wire:click="save"
                        class="flex-1 bg-blue-600 text-white text-sm py-2 rounded-lg hover:bg-blue-700">
                        Simpan
                    </button>
                    <button wire:click="cancel"
                        class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg hover:bg-gray-200">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- List --}}
    @forelse ($rtList as $rt)
        <div class="bg-white border rounded-xl p-4 mb-3 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-gray-800">{{ $rt['nama'] }}</p>
                    @if (!empty($rt['alamat']))
                        <p class="text-xs text-gray-500 mt-0.5">{{ $rt['alamat'] }}</p>
                    @endif
                </div>
                <div class="flex gap-2 ml-2 shrink-0">
                    <a href="{{ route('rt.korwil', $rt['id']) }}"
                        class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded-lg hover:bg-indigo-100">
                        Korwil
                    </a>
                    <button wire:click="openEdit('{{ $rt['id'] }}')"
                        class="text-xs bg-yellow-50 text-yellow-600 px-2 py-1 rounded-lg hover:bg-yellow-100">
                        Edit
                    </button>
                    <button wire:click="delete('{{ $rt['id'] }}')"
                        wire:confirm="Hapus RT ini beserta semua Korwil dan Role-nya?"
                        class="text-xs bg-red-50 text-red-600 px-2 py-1 rounded-lg hover:bg-red-100">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-10 text-gray-400 text-sm">
            Belum ada RT. Klik "+ Tambah RT" untuk mulai.
        </div>
    @endforelse
</div>
