<?php

namespace App\Livewire;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Livewire\Component;

class ManajemenRt extends Component
{
    public string $nama    = '';
    public string $alamat  = '';
    public string $editId  = '';
    public bool   $showForm = false;

    public function mount(): void {}

    private function firestore()
    {
        return Firebase::firestore()->database();
    }

    public function getRtList(): array
    {
        $docs = $this->firestore()->collection('rt')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if ($doc->exists()) {
                $list[] = array_merge(['id' => $doc->id()], $doc->data());
            }
        }
        return $list;
    }

    public function openCreate(): void
    {
        $this->reset(['nama', 'alamat', 'editId']);
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $doc = $this->firestore()->collection('rt')->document($id)->snapshot();
        if ($doc->exists()) {
            $data         = $doc->data();
            $this->editId = $id;
            $this->nama   = $data['nama'] ?? '';
            $this->alamat = $data['alamat'] ?? '';
            $this->showForm = true;
        }
    }

    public function save(): void
    {
        $this->validate([
            'nama'   => 'required|string|max:100',
            'alamat' => 'nullable|string|max:255',
        ]);

        $data = [
            'nama'       => $this->nama,
            'alamat'     => $this->alamat,
            'updated_at' => now()->toIso8601String(),
        ];

        $col = $this->firestore()->collection('rt');

        if ($this->editId) {
            $col->document($this->editId)->update(
                array_map(fn($k, $v) => ['path' => $k, 'value' => $v], array_keys($data), $data)
            );
        } else {
            $data['created_at'] = now()->toIso8601String();
            $col->add($data);
        }

        $this->reset(['nama', 'alamat', 'editId']);
        $this->showForm = false;
        session()->flash('success', 'RT berhasil disimpan.');
    }

    public function delete(string $id): void
    {
        // Hapus semua korwil dulu
        $korwils = $this->firestore()->collection('rt')->document($id)->collection('korwil')->documents();
        foreach ($korwils as $k) {
            // Hapus roles dalam korwil
            $roles = $k->reference()->collection('roles')->documents();
            foreach ($roles as $r) {
                $r->reference()->delete();
            }
            $k->reference()->delete();
        }
        $this->firestore()->collection('rt')->document($id)->delete();
        session()->flash('success', 'RT berhasil dihapus.');
    }

    public function cancel(): void
    {
        $this->reset(['nama', 'alamat', 'editId']);
        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.manajemen-rt', [
            'rtList' => $this->getRtList(),
        ]);
    }
}
