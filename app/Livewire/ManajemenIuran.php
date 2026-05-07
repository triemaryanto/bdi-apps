<?php

namespace App\Livewire;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Livewire\Component;

class ManajemenIuran extends Component
{
    public string $bulan;
    public int    $nominalDefault = 50000;
    public bool   $showBayarForm  = false;
    public string $bayarUid       = '';
    public string $bayarNama      = '';
    public int    $bayarNominal   = 0;
    public string $bayarMetode    = 'tunai';
    public string $bayarCatatan   = '';

    // Pengeluaran
    public bool   $showPengeluaran = false;
    public string $pengeluaranKet  = '';
    public int    $pengeluaranNom  = 0;

    public function mount(): void
    {
        $this->bulan = now()->format('Y-m');
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

    public function getTagihanList(): array
    {
        $rtId = $this->getRtId();
        $docs = $this->firestore()->collection('rt')->document($rtId)
            ->collection('iuran')->document($this->bulan)
            ->collection('tagihan')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if ($doc->exists()) $list[] = array_merge(['id' => $doc->id()], $doc->data());
        }
        return $list;
    }

    public function getRekap(): array
    {
        $tagihan = $this->getTagihanList();
        $lunas   = array_filter($tagihan, fn($t) => ($t['status'] ?? '') === 'lunas');
        $totalTagihan   = array_sum(array_column($tagihan, 'nominal'));
        $totalTerkumpul = array_sum(array_column(array_values($lunas), 'nominal'));

        // Pengeluaran bulan ini
        $rtId = $this->getRtId();
        $pengeluaranDocs = $this->firestore()->collection('rt')->document($rtId)
            ->collection('iuran')->document($this->bulan)
            ->collection('pengeluaran')->documents();
        $totalPengeluaran = 0;
        foreach ($pengeluaranDocs as $doc) {
            if ($doc->exists()) $totalPengeluaran += $doc->data()['nominal'] ?? 0;
        }

        return [
            'total_warga'      => count($tagihan),
            'lunas'            => count($lunas),
            'belum_lunas'      => count($tagihan) - count($lunas),
            'total_tagihan'    => $totalTagihan,
            'total_terkumpul'  => $totalTerkumpul,
            'total_pengeluaran'=> $totalPengeluaran,
            'saldo'            => $totalTerkumpul - $totalPengeluaran,
        ];
    }

    public function generateTagihan(): void
    {
        $rtId = $this->getRtId();
        // Ambil semua warga aktif
        $wargaDocs = $this->firestore()->collection('rt')->document($rtId)->collection('warga')->documents();
        $col = $this->firestore()->collection('rt')->document($rtId)
            ->collection('iuran')->document($this->bulan)->collection('tagihan');

        foreach ($wargaDocs as $doc) {
            if (! $doc->exists()) continue;
            $uid = $doc->id();
            $existing = $col->document($uid)->snapshot();
            if ($existing->exists()) continue; // skip jika sudah ada

            $col->document($uid)->set([
                'uid'        => $uid,
                'nama'       => $doc->data()['nama_kepala'] ?? $uid,
                'nominal'    => $this->nominalDefault,
                'status'     => 'belum',
                'bulan'      => $this->bulan,
                'created_at' => now()->toIso8601String(),
            ]);
        }
        session()->flash('success', 'Tagihan berhasil di-generate.');
    }

    public function openBayar(string $uid, string $nama, int $nominal): void
    {
        $this->bayarUid     = $uid;
        $this->bayarNama    = $nama;
        $this->bayarNominal = $nominal;
        $this->showBayarForm = true;
    }

    public function saveBayar(): void
    {
        $rtId = $this->getRtId();
        $this->firestore()->collection('rt')->document($rtId)
            ->collection('iuran')->document($this->bulan)
            ->collection('tagihan')->document($this->bayarUid)
            ->update([
                ['path' => 'status',       'value' => 'lunas'],
                ['path' => 'metode',       'value' => $this->bayarMetode],
                ['path' => 'catatan',      'value' => $this->bayarCatatan],
                ['path' => 'bayar_at',     'value' => now()->toIso8601String()],
            ]);

        $this->reset(['bayarUid', 'bayarNama', 'bayarNominal', 'bayarMetode', 'bayarCatatan']);
        $this->showBayarForm = false;
        session()->flash('success', 'Pembayaran berhasil dicatat.');
    }

    public function savePengeluaran(): void
    {
        $this->validate([
            'pengeluaranKet' => 'required|string|max:200',
            'pengeluaranNom' => 'required|integer|min:1',
        ]);

        $rtId = $this->getRtId();
        $this->firestore()->collection('rt')->document($rtId)
            ->collection('iuran')->document($this->bulan)
            ->collection('pengeluaran')->add([
                'keterangan' => $this->pengeluaranKet,
                'nominal'    => $this->pengeluaranNom,
                'created_at' => now()->toIso8601String(),
            ]);

        $this->reset(['pengeluaranKet', 'pengeluaranNom']);
        $this->showPengeluaran = false;
        session()->flash('success', 'Pengeluaran dicatat.');
    }

    public function render()
    {
        return view('livewire.manajemen-iuran', [
            'tagihanList' => $this->getTagihanList(),
            'rekap'       => $this->getRekap(),
        ]);
    }
}
