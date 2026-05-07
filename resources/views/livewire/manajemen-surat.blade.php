<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-2">{{ session('success') }}</div>
    @endif

    @php
        $statusColor = ['pending' => 'bg-yellow-100 text-yellow-700', 'diproses' => 'bg-blue-100 text-blue-700', 'selesai' => 'bg-green-100 text-green-700', 'ditolak' => 'bg-red-100 text-red-600'];
    @endphp

    <div class="flex justify-end mb-4">
        <button wire:click="$set('showForm', true)" class="bg-blue-600 text-white text-sm px-3 py-1.5 rounded-lg">+ Ajukan Surat</button>
    </div>

    {{-- Form Pengajuan --}}
    @if($showForm)
        <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm space-y-3">
            <h3 class="text-sm font-semibold text-gray-700">Ajukan Surat</h3>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Jenis Surat *</label>
                <select wire:model="jenis" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="domisili">Surat Domisili</option>
                    <option value="keterangan">Surat Keterangan</option>
                    <option value="pengantar">Surat Pengantar</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Keperluan *</label>
                <textarea wire:model="keperluan" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Jelaskan keperluan surat..."></textarea>
                @error('keperluan') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Upload Dokumen Pendukung</label>
                <input wire:model="dokumen" type="file" class="w-full text-sm">
            </div>
            <div class="flex gap-2">
                <button wire:click="ajukan" class="flex-1 bg-blue-600 text-white text-sm py-2 rounded-lg">Ajukan</button>
                <button wire:click="$set('showForm', false)" class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg">Batal</button>
            </div>
        </div>
    @endif

    {{-- List --}}
    @forelse($suratList as $s)
        <div class="bg-white border rounded-xl p-4 mb-3 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm capitalize">{{ str_replace('_', ' ', $s['jenis'] ?? '') }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $s['nama'] ?? '' }}</p>
                    <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ $s['keperluan'] ?? '' }}</p>
                    @if(!empty($s['catatan']))
                        <p class="text-xs text-blue-600 mt-1">Catatan: {{ $s['catatan'] }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($s['created_at'])->diffForHumans() }}</p>
                </div>
                <div class="ml-3 flex flex-col items-end gap-2">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColor[$s['status'] ?? 'pending'] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $s['status'] ?? 'pending' }}
                    </span>
                    @if($isAdmin)
                        <button wire:click="openUpdate('{{ $s['id'] }}', '{{ $s['status'] ?? 'pending' }}')"
                            class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded-lg">Update</button>
                    @endif
                    @if(!empty($s['dokumen']))
                        <a href="{{ $s['dokumen'] }}" target="_blank" class="text-xs text-gray-500 hover:text-gray-700">📎 Dok</a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-10 text-gray-400 text-sm">Belum ada pengajuan surat.</div>
    @endforelse

    {{-- Modal Update Status --}}
    @if($showUpdate)
        <div class="fixed inset-0 bg-black/40 flex items-end justify-center z-50">
            <div class="bg-white w-full max-w-lg rounded-t-2xl p-5 shadow-xl space-y-3">
                <h3 class="text-sm font-semibold text-gray-700">Update Status Surat</h3>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Status</label>
                    <select wire:model="updateStatus" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="pending">Pending</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Catatan</label>
                    <input wire:model="updateCatatan" type="text" class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="flex gap-2">
                    <button wire:click="saveUpdate" class="flex-1 bg-indigo-600 text-white text-sm py-2 rounded-lg">Simpan</button>
                    <button wire:click="$set('showUpdate', false)" class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg">Batal</button>
                </div>
            </div>
        </div>
    @endif
</div>
