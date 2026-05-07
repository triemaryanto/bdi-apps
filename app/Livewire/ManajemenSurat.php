<?php

namespace App\Livewire;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManajemenSurat extends Component
{
    use WithFileUploads;

    public string $jenis     = 'domisili';
    public string $keperluan = '';
    public        $dokumen   = null;
    public bool   $showForm  = false;

    // Untuk pengurus: update status
    public string $updateId     = '';
    public string $updateStatus = '';
    public string $updateCatatan = '';
    public bool   $showUpdate   = false;

    private function firestore()
    {
        return Firebase::firestore()->database();
    }

    private function getRtId(): string
    {
        $rtId = session('user_rt_id') ?? '';
        if (! $rtId) {
            foreach ($this->firestore()->collection('rt')->documents() as $rt) {
                if ($rt->exists()) { $rtId = $rt->id(); break; }
            }
        }
        return $rtId;
    }

    public function getSuratList(): array
    {
        $rtId = $this->getRtId();
        $col  = $this->firestore()->collection('rt')->document($rtId)->collection('surat');
        $uid  = session('firebase_uid');
        $role = session('user_role', 'warga');

        $docs = $col->documents();
        $list = [];
        foreach ($docs as $doc) {
            if (! $doc->exists()) continue;
            $data = $doc->data();
            // Warga hanya lihat suratnya sendiri
            if ($role === 'warga' && ($data['uid'] ?? '') !== $uid) continue;
            $list[] = array_merge(['id' => $doc->id()], $data);
        }
        usort($list, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return $list;
    }

    public function ajukan(): void
    {
        $this->validate([
            'jenis'     => 'required|string',
            'keperluan' => 'required|string|max:500',
            'dokumen'   => 'nullable|file|max:4096',
        ]);

        $dokUrl = '';
        if ($this->dokumen) {
            $dokUrl = $this->uploadToCloudinary($this->dokumen);
        }

        $rtId = $this->getRtId();
        $this->firestore()->collection('rt')->document($rtId)->collection('surat')->add([
            'uid'        => session('firebase_uid'),
            'nama'       => session('user_name'),
            'jenis'      => $this->jenis,
            'keperluan'  => $this->keperluan,
            'dokumen'    => $dokUrl,
            'status'     => 'pending',
            'catatan'    => '',
            'created_at' => now()->toIso8601String(),
        ]);

        $this->reset(['jenis', 'keperluan', 'dokumen']);
        $this->showForm = false;
        session()->flash('success', 'Surat berhasil diajukan.');
    }

    public function openUpdate(string $id, string $currentStatus): void
    {
        $this->updateId     = $id;
        $this->updateStatus = $currentStatus;
        $this->updateCatatan = '';
        $this->showUpdate   = true;
    }

    public function saveUpdate(): void
    {
        $this->validate(['updateStatus' => 'required|string']);

        $rtId = $this->getRtId();
        $this->firestore()->collection('rt')->document($rtId)->collection('surat')
            ->document($this->updateId)->update([
                ['path' => 'status',     'value' => $this->updateStatus],
                ['path' => 'catatan',    'value' => $this->updateCatatan],
                ['path' => 'updated_at', 'value' => now()->toIso8601String()],
            ]);

        // Notif FCM ke pemohon
        $this->notifSurat($this->updateId, $this->updateStatus);

        $this->reset(['updateId', 'updateStatus', 'updateCatatan']);
        $this->showUpdate = false;
        session()->flash('success', 'Status surat diperbarui.');
    }

    private function notifSurat(string $suratId, string $status): void
    {
        try {
            $rtId = $this->getRtId();
            $doc  = $this->firestore()->collection('rt')->document($rtId)
                ->collection('surat')->document($suratId)->snapshot();
            if (! $doc->exists()) return;

            $uid = $doc->data()['uid'] ?? '';
            if (! $uid) return;

            $userDoc = $this->firestore()->collection('users')->document($uid)->snapshot();
            $fcmToken = $userDoc->exists() ? ($userDoc->data()['fcm_token'] ?? '') : '';
            if (! $fcmToken) return;

            $messaging = Firebase::messaging();
            $message   = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification([
                    'title' => 'Update Surat',
                    'body'  => 'Status surat Anda: ' . strtoupper($status),
                ])
                ->withData(['url' => '/surat'])
                ->toToken($fcmToken);
            $messaging->send($message);
        } catch (\Throwable) {}
    }

    private function uploadToCloudinary($file): string
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');
        $timestamp = time();
        $signature = sha1("timestamp={$timestamp}" . $apiSecret);

        $response = \Illuminate\Support\Facades\Http::attach(
            'file', file_get_contents($file->getRealPath()), 'dokumen'
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/auto/upload", [
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        return $response->json('secure_url') ?? '';
    }

    public function render()
    {
        return view('livewire.manajemen-surat', [
            'suratList' => $this->getSuratList(),
            'isAdmin'   => in_array(session('user_role'), ['admin', 'superadmin', 'pengurus']),
        ]);
    }
}
