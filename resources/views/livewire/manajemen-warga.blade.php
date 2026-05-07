<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-2">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <select wire:model.live="filterKorwilId" class="border rounded-lg px-3 py-1.5 text-sm">
            <option value="">Semua Korwil</option>
            @foreach($korwilList as $k)
                <option value="{{ $k['id'] }}">{{ $k['nama'] }}</option>
            @endforeach
        </select>
        <button wire:click="openCreate" class="bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg">+ Tambah KK</button>
    </div>

    {{-- Form --}}
    @if($showForm)
        <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm space-y-3">
            <h3 class="text-sm font-semibold text-gray-700">{{ $editUid ? 'Edit' : 'Tambah' }} Data KK</h3>

            <div><label class="text-xs text-gray-500 block mb-1">No. KK *</label>
                <input wire:model="noKk" type="text" class="w-full border rounded-lg px-3 py-2 text-sm">
                @error('noKk') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
            <div><label class="text-xs text-gray-500 block mb-1">Nama Kepala Keluarga *</label>
                <input wire:model="namaKepala" type="text" class="w-full border rounded-lg px-3 py-2 text-sm">
                @error('namaKepala') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
            <div><label class="text-xs text-gray-500 block mb-1">Alamat *</label>
                <input wire:model="alamat" type="text" class="w-full border rounded-lg px-3 py-2 text-sm">
                @error('alamat') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
            <div><label class="text-xs text-gray-500 block mb-1">Status Hunian</label>
                <select wire:model="statusHunian" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="tetap">Tetap</option>
                    <option value="kontrak">Kontrak</option>
                    <option value="kos">Kos</option>
                </select>
            </div>
            <div><label class="text-xs text-gray-500 block mb-1">Foto KTP</label>
                <input wire:model="fotoKtp" type="file" accept="image/*" class="w-full text-sm">
                @if($fotoKtpUrl)
                    <img src="{{ $fotoKtpUrl }}" class="mt-2 h-20 rounded-lg object-cover" alt="KTP">
                @endif
                @error('fotoKtp') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- Anggota Keluarga --}}
            <div class="border-t pt-3">
                <p class="text-xs font-medium text-gray-600 mb-2">Anggota Keluarga</p>
                @foreach($anggota as $i => $a)
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-gray-700 flex-1">{{ $a['nama'] }} ({{ $a['hub'] }})</span>
                        <button wire:click="removeAnggota({{ $i }})" class="text-red-400 text-xs">✕</button>
                    </div>
                @endforeach
                <div class="flex gap-2 mt-2">
                    <input wire:model="anggotaNama" type="text" placeholder="Nama" class="flex-1 border rounded-lg px-2 py-1.5 text-xs">
                    <input wire:model="anggotaNik" type="text" placeholder="NIK" class="w-28 border rounded-lg px-2 py-1.5 text-xs">
                    <select wire:model="anggotaHub" class="border rounded-lg px-2 py-1.5 text-xs">
                        <option value="">Hub.</option>
                        <option value="Istri">Istri</option>
                        <option value="Anak">Anak</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <button wire:click="addAnggota" class="bg-gray-100 text-gray-700 text-xs px-2 py-1.5 rounded-lg">+</button>
                </div>
            </div>

            <div class="flex gap-2 pt-1">
                <button wire:click="save" class="flex-1 bg-blue-600 text-white text-sm py-2 rounded-lg">Simpan</button>
                <button wire:click="cancel" class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg">Batal</button>
            </div>
        </div>
    @endif

    {{-- List --}}
    @forelse($wargaList as $w)
        <div class="bg-white border rounded-xl p-4 mb-3 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $w['nama_kepala'] ?? '-' }}</p>
                    <p class="text-xs text-gray-500">KK: {{ $w['no_kk'] ?? '-' }}</p>
                    <p class="text-xs text-gray-500">{{ $w['alamat'] ?? '' }}</p>
                    @if(!empty($w['anggota']))
                        <p class="text-xs text-gray-400 mt-0.5">{{ count($w['anggota']) }} anggota keluarga</p>
                    @endif
                </div>
                <div class="flex gap-2 ml-2 shrink-0">
                    @if(!empty($w['foto_ktp']))
                        <a href="{{ $w['foto_ktp'] }}" target="_blank" class="text-xs bg-gray-50 text-gray-600 px-2 py-1 rounded-lg">📷</a>
                    @endif
                    <button wire:click="openEdit('{{ $w['id'] }}')" class="text-xs bg-yellow-50 text-yellow-600 px-2 py-1 rounded-lg">Edit</button>
                    <button wire:click="delete('{{ $w['id'] }}')" wire:confirm="Hapus data warga ini?" class="text-xs bg-red-50 text-red-600 px-2 py-1 rounded-lg">Hapus</button>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-10 text-gray-400 text-sm">Belum ada data warga.</div>
    @endforelse
</div>
