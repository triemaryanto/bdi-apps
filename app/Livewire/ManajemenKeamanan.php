<?php

namespace App\Livewire;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManajemenKeamanan extends Component
{
    use WithFileUploads;

    public string $tab = 'laporan'; // laporan | ronda

    // Laporan
    public string $laporanJudul = '';
    public string $laporanDeskripsi = '';
    public        $laporanFoto  = null;
    public bool   $showLaporanForm = false;

    // Ronda
    public string $rondaTanggal = '';
    public string $rondaPetugas = '';
    public string $rondaCatatan = '';
    public bool   $showRondaForm = false;

    public function mount(): void
    {
        $this->rondaTanggal = now()->format('Y-m-d');
    }

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

    public function getLaporanList(): array
    {
        $docs = $this->firestore()->collection('rt')->document($this->getRtId())
            ->collection('laporan')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if ($doc->exists()) $list[] = array_merge(['id' => $doc->id()], $doc->data());
        }
        usort($list, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return $list;
    }

    public function getRondaList(): array
    {
        $docs = $this->firestore()->collection('rt')->document($this->getRtId())
            ->collection('ronda')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if ($doc->exists()) $list[] = array_merge(['id' => $doc->id()], $doc->data());
        }
        usort($list, fn($a, $b) => strcmp($b['tanggal'] ?? '', $a['tanggal'] ?? ''));
        return $list;
    }

    public function saveLaporan(): void
    {
        $this->validate([
            'laporanJudul'     => 'required|string|max:200',
            'laporanDeskripsi' => 'required|string',
            'laporanFoto'      => 'nullable|image|max:4096',
        ]);

        $fotoUrl = '';
        if ($this->laporanFoto) {
            $fotoUrl = $this->uploadToCloudinary($this->laporanFoto);
        }

        $this->firestore()->collection('rt')->document($this->getRtId())
            ->collection('laporan')->add([
                'judul'      => $this->laporanJudul,
                'deskripsi'  => $this->laporanDeskripsi,
                'foto'       => $fotoUrl,
                'pelapor'    => session('user_name'),
                'uid'        => session('firebase_uid'),
                'created_at' => now()->toIso8601String(),
            ]);

        // Notif ke petugas keamanan
        $this->notifKeamanan($this->laporanJudul);

        $this->reset(['laporanJudul', 'laporanDeskripsi', 'laporanFoto']);
        $this->showLaporanForm = false;
        session()->flash('success', 'Laporan berhasil dikirim.');
    }

    public function saveRonda(): void
    {
        $this->validate([
            'rondaTanggal' => 'required|date',
            'rondaPetugas' => 'required|string|max:200',
        ]);

        $this->firestore()->collection('rt')->document($this->getRtId())
            ->collection('ronda')->add([
                'tanggal'    => $this->rondaTanggal,
                'petugas'    => $this->rondaPetugas,
                'catatan'    => $this->rondaCatatan,
                'created_at' => now()->toIso8601String(),
            ]);

        $this->reset(['rondaTanggal', 'rondaPetugas', 'rondaCatatan']);
        $this->showRondaForm = false;
        session()->flash('success', 'Jadwal ronda disimpan.');
    }

    public function deleteRonda(string $id): void
    {
        $this->firestore()->collection('rt')->document($this->getRtId())
            ->collection('ronda')->document($id)->delete();
    }

    private function notifKeamanan(string $judul): void
    {
        try {
            $messaging = Firebase::messaging();
            $message   = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification(['title' => '🚨 Laporan Keamanan', 'body' => $judul])
                ->withData(['url' => '/keamanan'])
                ->toTopic('rt-' . $this->getRtId() . '-keamanan');
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
            'file', file_get_contents($file->getRealPath()), 'foto.jpg'
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        return $response->json('secure_url') ?? '';
    }

    public function render()
    {
        return view('livewire.manajemen-keamanan', [
            'laporanList' => $this->tab === 'laporan' ? $this->getLaporanList() : [],
            'rondaList'   => $this->tab === 'ronda'   ? $this->getRondaList()   : [],
            'isAdmin'     => in_array(session('user_role'), ['admin', 'superadmin', 'pengurus']),
        ]);
    }
}
