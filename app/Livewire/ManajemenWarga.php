<?php

namespace App\Livewire;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManajemenWarga extends Component
{
    use WithFileUploads;

    // Filter
    public string $filterKorwilId = '';

    // Form KK
    public string $editUid    = '';
    public bool   $showForm   = false;
    public string $noKk       = '';
    public string $namaKepala = '';
    public string $alamat     = '';
    public string $statusHunian = 'tetap';
    public        $fotoKtp    = null;   // uploaded file
    public string $fotoKtpUrl = '';

    // Anggota keluarga
    public array $anggota = [];
    public string $anggotaNama = '';
    public string $anggotaNik  = '';
    public string $anggotaHub  = '';

    private function firestore()
    {
        return Firebase::firestore()->database();
    }

    public function mount(): void {}

    public function getKorwilList(): array
    {
        $rtId = session('user_rt_id') ?? '';
        if (! $rtId) {
            // Superadmin/admin: ambil semua RT lalu semua korwil (simplified: ambil RT pertama)
            $rts = $this->firestore()->collection('rt')->documents();
            foreach ($rts as $rt) {
                if ($rt->exists()) { $rtId = $rt->id(); break; }
            }
        }
        if (! $rtId) return [];
        $docs = $this->firestore()->collection('rt')->document($rtId)->collection('korwil')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if ($doc->exists()) $list[] = ['id' => $doc->id(), 'nama' => $doc->data()['nama'] ?? $doc->id()];
        }
        return $list;
    }

    public function getWargaList(): array
    {
        $rtId = session('user_rt_id') ?? '';
        if (! $rtId) {
            $rts = $this->firestore()->collection('rt')->documents();
            foreach ($rts as $rt) {
                if ($rt->exists()) { $rtId = $rt->id(); break; }
            }
        }
        if (! $rtId) return [];

        $col = $this->firestore()->collection('rt')->document($rtId)->collection('warga');
        $docs = $col->documents();
        $list = [];
        foreach ($docs as $doc) {
            if (! $doc->exists()) continue;
            $data = $doc->data();
            if ($this->filterKorwilId && ($data['korwil_id'] ?? '') !== $this->filterKorwilId) continue;
            $list[] = array_merge(['id' => $doc->id()], $data);
        }
        return $list;
    }

    public function openCreate(): void
    {
        $this->reset(['editUid', 'noKk', 'namaKepala', 'alamat', 'statusHunian', 'fotoKtp', 'fotoKtpUrl', 'anggota']);
        $this->showForm = true;
    }

    public function openEdit(string $uid): void
    {
        $rtId = $this->getRtId();
        $doc  = $this->firestore()->collection('rt')->document($rtId)->collection('warga')->document($uid)->snapshot();
        if ($doc->exists()) {
            $d = $doc->data();
            $this->editUid      = $uid;
            $this->noKk         = $d['no_kk'] ?? '';
            $this->namaKepala   = $d['nama_kepala'] ?? '';
            $this->alamat       = $d['alamat'] ?? '';
            $this->statusHunian = $d['status_hunian'] ?? 'tetap';
            $this->fotoKtpUrl   = $d['foto_ktp'] ?? '';
            $this->anggota      = $d['anggota'] ?? [];
            $this->showForm     = true;
        }
    }

    public function addAnggota(): void
    {
        if (! $this->anggotaNama) return;
        $this->anggota[] = [
            'nama' => $this->anggotaNama,
            'nik'  => $this->anggotaNik,
            'hub'  => $this->anggotaHub,
        ];
        $this->reset(['anggotaNama', 'anggotaNik', 'anggotaHub']);
    }

    public function removeAnggota(int $index): void
    {
        array_splice($this->anggota, $index, 1);
    }

    public function save(): void
    {
        $this->validate([
            'noKk'       => 'required|string|max:20',
            'namaKepala' => 'required|string|max:100',
            'alamat'     => 'required|string|max:255',
            'fotoKtp'    => 'nullable|image|max:2048',
        ]);

        $fotoUrl = $this->fotoKtpUrl;
        if ($this->fotoKtp) {
            $fotoUrl = $this->uploadToCloudinary($this->fotoKtp);
        }

        $rtId = $this->getRtId();
        $data = [
            'no_kk'         => $this->noKk,
            'nama_kepala'   => $this->namaKepala,
            'alamat'        => $this->alamat,
            'status_hunian' => $this->statusHunian,
            'foto_ktp'      => $fotoUrl,
            'anggota'       => $this->anggota,
            'updated_at'    => now()->toIso8601String(),
        ];

        $col = $this->firestore()->collection('rt')->document($rtId)->collection('warga');
        if ($this->editUid) {
            $col->document($this->editUid)->update(
                array_map(fn($k, $v) => ['path' => $k, 'value' => $v], array_keys($data), $data)
            );
        } else {
            $data['created_at'] = now()->toIso8601String();
            $col->add($data);
        }

        $this->reset(['editUid', 'noKk', 'namaKepala', 'alamat', 'fotoKtp', 'fotoKtpUrl', 'anggota']);
        $this->showForm = false;
        session()->flash('success', 'Data warga berhasil disimpan.');
    }

    public function delete(string $uid): void
    {
        $this->firestore()->collection('rt')->document($this->getRtId())
            ->collection('warga')->document($uid)->delete();
        session()->flash('success', 'Data warga dihapus.');
    }

    public function cancel(): void
    {
        $this->reset(['editUid', 'noKk', 'namaKepala', 'alamat', 'fotoKtp', 'fotoKtpUrl', 'anggota']);
        $this->showForm = false;
    }

    private function getRtId(): string
    {
        $rtId = session('user_rt_id') ?? '';
        if (! $rtId) {
            $rts = $this->firestore()->collection('rt')->documents();
            foreach ($rts as $rt) {
                if ($rt->exists()) { $rtId = $rt->id(); break; }
            }
        }
        return $rtId;
    }

    private function uploadToCloudinary($file): string
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');

        $timestamp = time();
        $params    = "timestamp={$timestamp}" . $apiSecret;
        $signature = sha1($params);

        $response = \Illuminate\Support\Facades\Http::attach(
            'file', file_get_contents($file->getRealPath()), 'upload.jpg'
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        return $response->json('secure_url') ?? '';
    }

    public function render()
    {
        return view('livewire.manajemen-warga', [
            'wargaList'  => $this->getWargaList(),
            'korwilList' => $this->getKorwilList(),
        ]);
    }
}
