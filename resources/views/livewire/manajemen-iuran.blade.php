<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-2">{{ session('success') }}</div>
    @endif

    {{-- Bulan Selector --}}
    <div class="flex items-center gap-3 mb-4">
        <input wire:model.live="bulan" type="month" class="border rounded-lg px-3 py-2 text-sm flex-1">
        <button wire:click="generateTagihan" class="bg-blue-600 text-white text-sm px-3 py-2 rounded-lg whitespace-nowrap">Generate Tagihan</button>
    </div>

    {{-- Rekap --}}
    <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm">
        <p class="text-xs font-medium text-gray-500 uppercase mb-3">Rekap {{ $bulan }}</p>
        <div class="grid grid-cols-2 gap-3">
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">{{ $rekap['lunas'] }}</p>
                <p class="text-xs text-gray-500">Lunas</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-red-500">{{ $rekap['belum_lunas'] }}</p>
                <p class="text-xs text-gray-500">Belum Lunas</p>
            </div>
            <div class="text-center col-span-2 border-t pt-3">
                <p class="text-lg font-bold text-blue-700">Rp {{ number_format($rekap['total_terkumpul'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500">Terkumpul dari Rp {{ number_format($rekap['total_tagihan'], 0, ',', '.') }}</p>
            </div>
            <div class="text-center">
                <p class="text-base font-semibold text-orange-600">Rp {{ number_format($rekap['total_pengeluaran'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500">Pengeluaran</p>
            </div>
            <div class="text-center">
                <p class="text-base font-semibold text-teal-600">Rp {{ number_format($rekap['saldo'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500">Saldo</p>
            </div>
        </div>
        <button wire:click="$set('showPengeluaran', true)" class="mt-3 w-full text-xs bg-orange-50 text-orange-600 py-2 rounded-lg">+ Catat Pengeluaran</button>
    </div>

    {{-- Form Pengeluaran --}}
    @if($showPengeluaran)
        <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm space-y-3">
            <h3 class="text-sm font-semibold text-gray-700">Catat Pengeluaran</h3>
            <input wire:model="pengeluaranKet" type="text" placeholder="Keterangan" class="w-full border rounded-lg px-3 py-2 text-sm">
            @error('pengeluaranKet') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            <input wire:model="pengeluaranNom" type="number" placeholder="Nominal (Rp)" class="w-full border rounded-lg px-3 py-2 text-sm">
            @error('pengeluaranNom') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            <div class="flex gap-2">
                <button wire:click="savePengeluaran" class="flex-1 bg-orange-500 text-white text-sm py-2 rounded-lg">Simpan</button>
                <button wire:click="$set('showPengeluaran', false)" class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg">Batal</button>
            </div>
        </div>
    @endif

    {{-- Tagihan List --}}
    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Daftar Tagihan</p>
    @forelse($tagihanList as $t)
        <div class="bg-white border rounded-xl p-3 mb-2 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-800">{{ $t['nama'] ?? $t['uid'] }}</p>
                <p class="text-xs text-gray-500">Rp {{ number_format($t['nominal'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full {{ ($t['status'] ?? '') === 'lunas' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ ($t['status'] ?? 'belum') === 'lunas' ? 'Lunas' : 'Belum' }}
                </span>
                @if(($t['status'] ?? '') !== 'lunas')
                    <button wire:click="openBayar('{{ $t['id'] }}', '{{ $t['nama'] ?? '' }}', {{ $t['nominal'] ?? 0 }})"
                        class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded-lg">Bayar</button>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-gray-400 text-sm">Belum ada tagihan. Klik "Generate Tagihan".</div>
    @endforelse

    {{-- Modal Bayar --}}
    @if($showBayarForm)
        <div class="fixed inset-0 bg-black/40 flex items-end justify-center z-50">
            <div class="bg-white w-full max-w-lg rounded-t-2xl p-5 shadow-xl space-y-3">
                <h3 class="text-sm font-semibold text-gray-700">Catat Pembayaran — {{ $bayarNama }}</h3>
                <p class="text-sm text-gray-600">Nominal: <strong>Rp {{ number_format($bayarNominal, 0, ',', '.') }}</strong></p>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Metode</label>
                    <select wire:model="bayarMetode" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="tunai">Tunai</option>
                        <option value="transfer">Transfer</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Catatan</label>
                    <input wire:model="bayarCatatan" type="text" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="flex gap-2">
                    <button wire:click="saveBayar" class="flex-1 bg-green-600 text-white text-sm py-2 rounded-lg">Konfirmasi Lunas</button>
                    <button wire:click="$set('showBayarForm', false)" class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg">Batal</button>
                </div>
            </div>
        </div>
    @endif
</div>
