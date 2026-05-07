<div>
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-2">{{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <div class="flex gap-2 mb-4">
        @foreach(['pending' => 'Pending', 'active' => 'Aktif', 'all' => 'Semua'] as $val => $label)
            <button wire:click="$set('filterStatus', '{{ $val }}')"
                class="text-xs px-3 py-1.5 rounded-full border {{ $filterStatus === $val ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- User List --}}
    @forelse($users as $user)
        <div class="bg-white border rounded-xl p-4 mb-3 shadow-sm">
            <div class="flex items-center gap-3">
                @if(!empty($user['photo']))
                    <img src="{{ $user['photo'] }}" class="w-10 h-10 rounded-full shrink-0" alt="">
                @else
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-sm shrink-0">👤</div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 text-sm truncate">{{ $user['name'] ?? 'Tanpa Nama' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $user['email'] ?? '' }}</p>
                    <div class="flex gap-2 mt-1">
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ ($user['status'] ?? 'pending') === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $user['status'] ?? 'pending' }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                            {{ $user['role'] ?? 'warga' }}
                        </span>
                    </div>
                </div>
                <button wire:click="openAssign('{{ $user['id'] }}')"
                    class="text-xs bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-100 shrink-0">
                    Assign
                </button>
            </div>
        </div>
    @empty
        <div class="text-center py-10 text-gray-400 text-sm">Tidak ada user dengan status "{{ $filterStatus }}".</div>
    @endforelse

    {{-- Modal Assign --}}
    @if($showAssign)
        <div class="fixed inset-0 bg-black/40 flex items-end justify-center z-50">
            <div class="bg-white w-full max-w-lg rounded-t-2xl p-5 shadow-xl">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Assign User ke Korwil</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">RT *</label>
                        <select wire:model.live="assignRtId" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih RT --</option>
                            @foreach($rtList as $rt)
                                <option value="{{ $rt['id'] }}">{{ $rt['nama'] }}</option>
                            @endforeach
                        </select>
                        @error('assignRtId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Korwil *</label>
                        <select wire:model="assignKorwilId" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">-- Pilih Korwil --</option>
                            @foreach($korwilList as $k)
                                <option value="{{ $k['id'] }}">{{ $k['nama'] }}</option>
                            @endforeach
                        </select>
                        @error('assignKorwilId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Role *</label>
                        <select wire:model="assignRole" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="warga">Warga</option>
                            <option value="pengurus">Pengurus</option>
                            <option value="admin">Admin RT</option>
                        </select>
                    </div>
                    <div class="flex gap-2 pt-1">
                        <button wire:click="saveAssign" class="flex-1 bg-blue-600 text-white text-sm py-2 rounded-lg">Simpan</button>
                        <button wire:click="cancelAssign" class="flex-1 bg-gray-100 text-gray-700 text-sm py-2 rounded-lg">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
