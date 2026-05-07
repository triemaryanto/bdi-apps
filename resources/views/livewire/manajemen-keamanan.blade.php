<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-2">{{ session('success') }}</div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-2 mb-4">
        <button wire:click="$set('tab', 'laporan')"
            class="flex-1 text-sm py-2 rounded-lg {{ $tab === 'laporan' ? 'bg-red-600 text-white' : 'bg-white border text-gray-600' }}">
            🚨 Laporan
        </button>
        <button wire:click="$set('tab', 'ronda')"
            class="flex-1 text-sm py-2 rounded-lg {{ $tab === 'ronda' ? 'bg-indigo-600 text-white' : 'bg-white border text-gray-600' }}">
            🌙 Jadwal Ronda
        </button>
    </div>

    {{-- TAB: LAPORAN --}}
    @if($tab === 'laporan')
        <div class="flex justify-end mb-3">
            <button wire:click="$set('showLaporanForm', true)" class="bg-red-600 text-white text-sm px-3 py-1.5 rounded-lg">+ Lapor Kejadian</button>
        </div>

        @if($showLaporanForm)
            <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm space-y-3">
                <h3 class="text-sm font-semibold text-gray-700">Laporan Kejadian</h3>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Judul *</label>
                    <input wire:model="laporanJudul" type="text" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @error('laporanJudul') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Deskripsi *</label>
                    <textarea wire:model="laporanDeskripsi" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    @error('laporanDeskripsi') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Foto</label>
                    <input wire:model="laporanFoto" type="file" accept="image/*" class="w-full text-sm">
                </div>
                <div class="flex gap-2">
                    <button wire:click="saveLaporan" class="flex-1 bg-red-600 text-white text-sm py-2 rounded-lg">Kirim Laporan</button>
                    <button wire:click="$set('showLaporanForm', false)" class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg">Batal</button>
                </div>
            </div>
        @endif

        @forelse($laporanList as $l)
            <div class="bg-white border rounded-xl p-4 mb-3 shadow-sm">
                @if(!empty($l['foto']))
                    <img src="{{ $l['foto'] }}" class="w-full h-36 object-cover rounded-lg mb-3" alt="">
                @endif
                <p class="font-semibold text-gray-800 text-sm">{{ $l['judul'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $l['pelapor'] ?? '' }} · {{ \Carbon\Carbon::parse($l['created_at'])->diffForHumans() }}</p>
                <p class="text-sm text-gray-700 mt-2">{{ $l['deskripsi'] }}</p>
            </div>
        @empty
            <div class="text-center py-10 text-gray-400 text-sm">Belum ada laporan kejadian.</div>
        @endforelse
    @endif

    {{-- TAB: RONDA --}}
    @if($tab === 'ronda')
        @if($isAdmin)
            <div class="flex justify-end mb-3">
                <button wire:click="$set('showRondaForm', true)" class="bg-indigo-600 text-white text-sm px-3 py-1.5 rounded-lg">+ Jadwal Ronda</button>
            </div>
        @endif

        @if($showRondaForm)
            <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm space-y-3">
                <h3 class="text-sm font-semibold text-gray-700">Tambah Jadwal Ronda</h3>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Tanggal *</label>
                    <input wire:model="rondaTanggal" type="date" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Petugas *</label>
                    <input wire:model="rondaPetugas" type="text" placeholder="Nama petugas, pisah koma" class="w-full border rounded-lg px-3 py-2 text-sm">
                    @error('rondaPetugas') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Catatan</label>
                    <input wire:model="rondaCatatan" type="text" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="flex gap-2">
                    <button wire:click="saveRonda" class="flex-1 bg-indigo-600 text-white text-sm py-2 rounded-lg">Simpan</button>
                    <button wire:click="$set('showRondaForm', false)" class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg">Batal</button>
                </div>
            </div>
        @endif

        @forelse($rondaList as $r)
            <div class="bg-white border rounded-xl p-4 mb-3 shadow-sm flex items-start justify-between">
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ \Carbon\Carbon::parse($r['tanggal'])->translatedFormat('l, d M Y') }}</p>
                    <p class="text-xs text-gray-600 mt-1">👮 {{ $r['petugas'] }}</p>
                    @if(!empty($r['catatan']))
                        <p class="text-xs text-gray-500 mt-0.5">{{ $r['catatan'] }}</p>
                    @endif
                </div>
                @if($isAdmin)
                    <button wire:click="deleteRonda('{{ $r['id'] }}')" wire:confirm="Hapus jadwal ini?"
                        class="text-xs text-red-400 hover:text-red-600 ml-2">✕</button>
                @endif
            </div>
        @empty
            <div class="text-center py-10 text-gray-400 text-sm">Belum ada jadwal ronda.</div>
        @endforelse
    @endif
</div>
