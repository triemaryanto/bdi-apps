<?php

namespace App\Livewire;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Livewire\Component;

class ManajemenUser extends Component
{
    public string $filterStatus = 'pending';
    public string $assignUid    = '';
    public string $assignRtId   = '';
    public string $assignKorwilId = '';
    public string $assignRole   = 'warga';
    public bool   $showAssign   = false;

    private function firestore()
    {
        return Firebase::firestore()->database();
    }

    public function getUsers(): array
    {
        $docs = $this->firestore()->collection('users')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if (! $doc->exists()) continue;
            $data = $doc->data();
            if ($this->filterStatus === 'all' || ($data['status'] ?? 'pending') === $this->filterStatus) {
                $list[] = array_merge(['id' => $doc->id()], $data);
            }
        }
        return $list;
    }

    public function getRtList(): array
    {
        $docs = $this->firestore()->collection('rt')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if ($doc->exists()) $list[] = ['id' => $doc->id(), 'nama' => $doc->data()['nama'] ?? $doc->id()];
        }
        return $list;
    }

    public function getKorwilList(): array
    {
        if (! $this->assignRtId) return [];
        $docs = $this->firestore()->collection('rt')->document($this->assignRtId)->collection('korwil')->documents();
        $list = [];
        foreach ($docs as $doc) {
            if ($doc->exists()) $list[] = ['id' => $doc->id(), 'nama' => $doc->data()['nama'] ?? $doc->id()];
        }
        return $list;
    }

    public function openAssign(string $uid): void
    {
        $this->assignUid = $uid;
        $snap = $this->firestore()->collection('users')->document($uid)->snapshot();
        if ($snap->exists()) {
            $data = $snap->data();
            $this->assignRtId     = $data['rt_id'] ?? '';
            $this->assignKorwilId = $data['korwil_id'] ?? '';
            $this->assignRole     = $data['role'] ?? 'warga';
        }
        $this->showAssign = true;
    }

    public function saveAssign(): void
    {
        $this->validate([
            'assignRtId'     => 'required|string',
            'assignKorwilId' => 'required|string',
            'assignRole'     => 'required|string',
        ]);

        $fs  = $this->firestore();
        $uid = $this->assignUid;

        $fs->collection('users')->document($uid)->update([
            ['path' => 'rt_id',     'value' => $this->assignRtId],
            ['path' => 'korwil_id', 'value' => $this->assignKorwilId],
            ['path' => 'role',      'value' => $this->assignRole],
            ['path' => 'status',    'value' => 'active'],
        ]);

        // Simpan juga ke /rt/{rtId}/warga/{uid}
        $fs->collection('rt')->document($this->assignRtId)
            ->collection('warga')->document($uid)
            ->set([
                'uid'        => $uid,
                'korwil_id'  => $this->assignKorwilId,
                'role'       => $this->assignRole,
                'updated_at' => now()->toIso8601String(),
            ], ['merge' => true]);

        $this->reset(['assignUid', 'assignRtId', 'assignKorwilId', 'assignRole']);
        $this->showAssign = false;
        session()->flash('success', 'User berhasil di-assign.');
    }

    public function cancelAssign(): void
    {
        $this->reset(['assignUid', 'assignRtId', 'assignKorwilId', 'assignRole']);
        $this->showAssign = false;
    }

    public function render()
    {
        return view('livewire.manajemen-user', [
            'users'      => $this->getUsers(),
            'rtList'     => $this->getRtList(),
            'korwilList' => $this->getKorwilList(),
        ]);
    }
}
