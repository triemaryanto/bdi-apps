<?php

namespace App\Livewire;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManajemenPengumuman extends Component
{
    use WithFileUploads;

    public string $judul    = '';
    public string $isi      = '';
    public        $gambar   = null;
    public bool   $showForm = false;

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

    public function getPengumumanList(): array
    {
        $rtId = $this->getRtId();
        $docs = $this->firestore()->collection('rt')->document($rtId)->collection('pengumuman')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if ($doc->exists()) $list[] = array_merge(['id' => $doc->id()], $doc->data());
        }
        usort($list, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return $list;
    }

    public function save(): void
    {
        $this->validate([
            'judul'  => 'required|string|max:200',
            'isi'    => 'required|string',
            'gambar' => 'nullable|image|max:2048',
        ]);

        $gambarUrl = '';
        if ($this->gambar) {
            $gambarUrl = $this->uploadToCloudinary($this->gambar);
        }

        $rtId = $this->getRtId();
        $ref  = $this->firestore()->collection('rt')->document($rtId)->collection('pengumuman')->add([
            'judul'      => $this->judul,
            'isi'        => $this->isi,
            'gambar'     => $gambarUrl,
            'author'     => session('user_name'),
            'created_at' => now()->toIso8601String(),
        ]);

        // FCM Broadcast
        $this->broadcastFcm($this->judul, $this->isi, '/pengumuman');

        $this->reset(['judul', 'isi', 'gambar']);
        $this->showForm = false;
        session()->flash('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function delete(string $id): void
    {
        $this->firestore()->collection('rt')->document($this->getRtId())
            ->collection('pengumuman')->document($id)->delete();
        session()->flash('success', 'Pengumuman dihapus.');
    }

    private function broadcastFcm(string $title, string $body, string $url = '/'): void
    {
        try {
            $messaging = Firebase::messaging();
            $message   = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification(['title' => $title, 'body' => mb_substr($body, 0, 100)])
                ->withData(['url' => $url])
                ->toTopic('rt-' . $this->getRtId());
            $messaging->send($message);
        } catch (\Throwable $e) {
            // FCM gagal tidak harus block save
        }
    }

    private function uploadToCloudinary($file): string
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');
        $timestamp = time();
        $signature = sha1("timestamp={$timestamp}" . $apiSecret);

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
        return view('livewire.manajemen-pengumuman', [
            'pengumumanList' => $this->getPengumumanList(),
        ]);
    }
}
