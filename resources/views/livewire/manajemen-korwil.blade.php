<div>
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-2">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-semibold text-gray-800">Daftar Korwil</h2>
        <button wire:click="openCreate"
            class="bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg hover:bg-blue-700">
            + Tambah Korwil
        </button>
    </div>

    {{-- Form Korwil --}}
    @if ($showForm)
        <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">
                {{ $editId ? 'Edit Korwil' : 'Tambah Korwil Baru' }}
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Nama Korwil *</label>
                    <input wire:model="nama" type="text" placeholder="cth: Korwil A"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Deskripsi</label>
                    <input wire:model="deskripsi" type="text" placeholder="cth: Blok A, RW 02"
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

    {{-- List Korwil --}}
    @forelse ($korwilList as $korwil)
        <div class="bg-white border rounded-xl p-4 mb-3 shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-semibold text-gray-800">{{ $korwil['nama'] }}</p>
                    @if (!empty($korwil['deskripsi']))
                        <p class="text-xs text-gray-500 mt-0.5">{{ $korwil['deskripsi'] }}</p>
                    @endif
                </div>
                <div class="flex gap-2 ml-2 shrink-0">
                    <button wire:click="openEdit('{{ $korwil['id'] }}')"
                        class="text-xs bg-yellow-50 text-yellow-600 px-2 py-1 rounded-lg hover:bg-yellow-100">
                        Edit
                    </button>
                    <button wire:click="delete('{{ $korwil['id'] }}')"
                        wire:confirm="Hapus Korwil ini beserta semua Role-nya?"
                        class="text-xs bg-red-50 text-red-600 px-2 py-1 rounded-lg hover:bg-red-100">
                        Hapus
                    </button>
                </div>
            </div>

            {{-- Role List --}}
            <div class="border-t pt-3">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Role Custom</p>
                    <button wire:click="openRoleManager('{{ $korwil['id'] }}')"
                        class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded-lg hover:bg-indigo-100">
                        + Role
                    </button>
                </div>

                @if (!empty($rolesByKorwil[$korwil['id']]))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($rolesByKorwil[$korwil['id']] as $role)
                            <div class="flex items-center gap-1 bg-gray-50 border rounded-lg px-2 py-1">
                                <span class="text-xs text-gray-700">{{ $role['nama'] }}</span>
                                <button wire:click="openEditRole('{{ $korwil['id'] }}', '{{ $role['id'] }}')"
                                    class="text-yellow-500 hover:text-yellow-700 text-xs ml-1">✏️</button>
                                <button wire:click="deleteRole('{{ $korwil['id'] }}', '{{ $role['id'] }}')"
                                    wire:confirm="Hapus role ini?"
                                    class="text-red-400 hover:text-red-600 text-xs">✕</button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic">Belum ada role.</p>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-10 text-gray-400 text-sm">
            Belum ada Korwil. Klik "+ Tambah Korwil" untuk mulai.
        </div>
    @endforelse

    {{-- Modal Form Role --}}
    @if ($showRoleForm)
        <div class="fixed inset-0 bg-black/40 flex items-end justify-center z-50">
            <div class="bg-white w-full max-w-lg rounded-t-2xl p-5 shadow-xl">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    {{ $editRoleId ? 'Edit Role' : 'Tambah Role Baru' }}
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Nama Role *</label>
                        <input wire:model="roleName" type="text" placeholder="cth: Ketua, Bendahara, Keamanan"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @error('roleName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Deskripsi</label>
                        <input wire:model="roleDesc" type="text" placeholder="Deskripsi singkat role"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div class="flex gap-2 pt-1">
                        <button wire:click="saveRole"
                            class="flex-1 bg-indigo-600 text-white text-sm py-2 rounded-lg hover:bg-indigo-700">
                            Simpan
                        </button>
                        <button wire:click="closeRoleForm"
                            class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg hover:bg-gray-200">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
