<?php

namespace App\Livewire;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Livewire\Component;

class ManajemenKorwil extends Component
{
    public string $rtId;
    public string $nama        = '';
    public string $deskripsi   = '';
    public string $editId      = '';
    public bool   $showForm    = false;
    public bool   $showRoleForm = false;
    public string $selectedKorwilId = '';
    public string $roleName    = '';
    public string $roleDesc    = '';
    public string $editRoleId  = '';

    public function mount(string $rtId): void
    {
        $this->rtId = $rtId;
    }

    private function firestore()
    {
        return Firebase::firestore()->database();
    }

    public function getKorwilList(): array
    {
        $docs = $this->firestore()->collection('rt')->document($this->rtId)->collection('korwil')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if ($doc->exists()) {
                $list[] = array_merge(['id' => $doc->id()], $doc->data());
            }
        }
        return $list;
    }

    public function getRoleList(string $korwilId): array
    {
        $docs = $this->firestore()->collection('rt')->document($this->rtId)
            ->collection('korwil')->document($korwilId)
            ->collection('roles')->documents();
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
        $this->reset(['nama', 'deskripsi', 'editId']);
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $doc = $this->firestore()->collection('rt')->document($this->rtId)
            ->collection('korwil')->document($id)->snapshot();
        if ($doc->exists()) {
            $data           = $doc->data();
            $this->editId   = $id;
            $this->nama     = $data['nama'] ?? '';
            $this->deskripsi = $data['deskripsi'] ?? '';
            $this->showForm = true;
        }
    }

    public function save(): void
    {
        $this->validate([
            'nama'      => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $data = [
            'nama'       => $this->nama,
            'deskripsi'  => $this->deskripsi,
            'updated_at' => now()->toIso8601String(),
        ];

        $col = $this->firestore()->collection('rt')->document($this->rtId)->collection('korwil');

        if ($this->editId) {
            $col->document($this->editId)->update(
                array_map(fn($k, $v) => ['path' => $k, 'value' => $v], array_keys($data), $data)
            );
        } else {
            $data['created_at'] = now()->toIso8601String();
            $col->add($data);
        }

        $this->reset(['nama', 'deskripsi', 'editId']);
        $this->showForm = false;
        session()->flash('success', 'Korwil berhasil disimpan.');
    }

    public function delete(string $id): void
    {
        // Hapus roles dulu
        $roles = $this->firestore()->collection('rt')->document($this->rtId)
            ->collection('korwil')->document($id)
            ->collection('roles')->documents();
        foreach ($roles as $r) {
            $r->reference()->delete();
        }
        $this->firestore()->collection('rt')->document($this->rtId)
            ->collection('korwil')->document($id)->delete();
        session()->flash('success', 'Korwil berhasil dihapus.');
    }

    public function cancel(): void
    {
        $this->reset(['nama', 'deskripsi', 'editId']);
        $this->showForm = false;
    }

    // Role Management
    public function openRoleManager(string $korwilId): void
    {
        $this->selectedKorwilId = $korwilId;
        $this->showRoleForm = true;
        $this->reset(['roleName', 'roleDesc', 'editRoleId']);
    }

    public function openEditRole(string $korwilId, string $roleId): void
    {
        $doc = $this->firestore()->collection('rt')->document($this->rtId)
            ->collection('korwil')->document($korwilId)
            ->collection('roles')->document($roleId)->snapshot();
        if ($doc->exists()) {
            $data                   = $doc->data();
            $this->selectedKorwilId = $korwilId;
            $this->editRoleId       = $roleId;
            $this->roleName         = $data['nama'] ?? '';
            $this->roleDesc         = $data['deskripsi'] ?? '';
            $this->showRoleForm     = true;
        }
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleName' => 'required|string|max:100',
            'roleDesc' => 'nullable|string|max:255',
        ]);

        $data = [
            'nama'       => $this->roleName,
            'deskripsi'  => $this->roleDesc,
            'updated_at' => now()->toIso8601String(),
        ];

        $col = $this->firestore()->collection('rt')->document($this->rtId)
            ->collection('korwil')->document($this->selectedKorwilId)
            ->collection('roles');

        if ($this->editRoleId) {
            $col->document($this->editRoleId)->update(
                array_map(fn($k, $v) => ['path' => $k, 'value' => $v], array_keys($data), $data)
            );
        } else {
            $data['created_at'] = now()->toIso8601String();
            $col->add($data);
        }

        $this->reset(['roleName', 'roleDesc', 'editRoleId']);
        session()->flash('success', 'Role berhasil disimpan.');
    }

    public function deleteRole(string $korwilId, string $roleId): void
    {
        $this->firestore()->collection('rt')->document($this->rtId)
            ->collection('korwil')->document($korwilId)
            ->collection('roles')->document($roleId)->delete();
        session()->flash('success', 'Role berhasil dihapus.');
    }

    public function closeRoleForm(): void
    {
        $this->reset(['roleName', 'roleDesc', 'editRoleId', 'selectedKorwilId']);
        $this->showRoleForm = false;
    }

    public function render()
    {
        $korwilList = $this->getKorwilList();
        $rolesByKorwil = [];
        foreach ($korwilList as $k) {
            $rolesByKorwil[$k['id']] = $this->getRoleList($k['id']);
        }

        return view('livewire.manajemen-korwil', [
            'korwilList'    => $korwilList,
            'rolesByKorwil' => $rolesByKorwil,
        ]);
    }
}
