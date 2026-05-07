<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-2">{{ session('success') }}</div>
    @endif

    @php $isAdmin = in_array(session('user_role'), ['admin', 'superadmin', 'pengurus']); @endphp

    @if($isAdmin)
        <div class="flex justify-end mb-4">
            <button wire:click="$set('showForm', true)" class="bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg">+ Buat Pengumuman</button>
        </div>
    @endif

    {{-- Form --}}
    @if($showForm)
        <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm space-y-3">
            <h3 class="text-sm font-semibold text-gray-700">Buat Pengumuman</h3>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Judul *</label>
                <input wire:model="judul" type="text" class="w-full border rounded-lg px-3 py-2 text-sm">
                @error('judul') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Isi *</label>
                <textarea wire:model="isi" rows="4" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                @error('isi') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Gambar (opsional)</label>
                <input wire:model="gambar" type="file" accept="image/*" class="w-full text-sm">
            </div>
            <div class="flex gap-2">
                <button wire:click="save" class="flex-1 bg-blue-600 text-white text-sm py-2 rounded-lg">Publikasikan + Kirim Notif</button>
                <button wire:click="$set('showForm', false)" class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg">Batal</button>
            </div>
        </div>
    @endif

    {{-- Feed --}}
    @forelse($pengumumanList as $p)
        <div class="bg-white border rounded-xl p-4 mb-3 shadow-sm">
            @if(!empty($p['gambar']))
                <img src="{{ $p['gambar'] }}" class="w-full h-40 object-cover rounded-lg mb-3" alt="">
            @endif
            <p class="font-semibold text-gray-800 text-sm">{{ $p['judul'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5 mb-2">{{ $p['author'] ?? '' }} · {{ \Carbon\Carbon::parse($p['created_at'])->diffForHumans() }}</p>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $p['isi'] }}</p>
            @if($isAdmin)
                <div class="mt-3 flex justify-end">
                    <button wire:click="delete('{{ $p['id'] }}')" wire:confirm="Hapus pengumuman ini?"
                        class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-10 text-gray-400 text-sm">Belum ada pengumuman.</div>
    @endforelse
</div>
