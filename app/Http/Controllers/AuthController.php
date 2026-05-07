<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;

class AuthController extends Controller
{
    public function firebaseLogin(Request $request)
    {
        $request->validate(['id_token' => 'required|string']);

        try {
            $auth      = Firebase::auth();
            $verified  = $auth->verifyIdToken($request->id_token);
            $uid       = $verified->claims()->get('sub');
            $email     = $verified->claims()->get('email');
            $name      = $verified->claims()->get('name');
            $photo     = $verified->claims()->get('picture');

            $firestore = Firebase::firestore()->database();
            $userRef   = $firestore->collection('users')->document($uid);
            $userSnap  = $userRef->snapshot();

            // Tentukan role
            $superadminEmail = env('SUPERADMIN_EMAIL');
            $isSuperadmin    = $superadminEmail && strtolower($email) === strtolower($superadminEmail);

            if (! $userSnap->exists()) {
                // User baru — simpan ke Firestore
                $userRef->set([
                    'uid'        => $uid,
                    'email'      => $email,
                    'name'       => $name,
                    'photo'      => $photo,
                    'role'       => $isSuperadmin ? 'superadmin' : 'warga',
                    'korwil_id'  => null,
                    'rt_id'      => null,
                    'status'     => $isSuperadmin ? 'active' : 'pending',
                    'created_at' => now()->toIso8601String(),
                ]);
                $role   = $isSuperadmin ? 'superadmin' : 'warga';
                $status = $isSuperadmin ? 'active' : 'pending';
            } else {
                $data = $userSnap->data();
                $role = $data['role'] ?? 'warga';

                // Upgrade ke superadmin jika email cocok tapi role belum superadmin
                if ($isSuperadmin && $role !== 'superadmin') {
                    $userRef->update([['path' => 'role', 'value' => 'superadmin'],
                                      ['path' => 'status', 'value' => 'active']]);
                    $role = 'superadmin';
                }

                $status = $data['status'] ?? 'pending';
            }

            // Simpan session Laravel
            session([
                'firebase_uid'  => $uid,
                'user_email'    => $email,
                'user_name'     => $name,
                'user_photo'    => $photo,
                'user_role'     => $role,
                'user_status'   => $status,
            ]);

            return response()->json(['redirect' => $this->redirectByRole($role, $status)]);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Token tidak valid'], 401);
        }
    }

    public function saveFcmToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $uid = session('firebase_uid');
        if ($uid) {
            Firebase::firestore()->database()
                ->collection('users')->document($uid)
                ->update([['path' => 'fcm_token', 'value' => $request->token]]);
        }
        return response()->json(['ok' => true]);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect('/login');
    }

    private function redirectByRole(string $role, string $status): string
    {
        if ($status === 'pending') return '/pending';

        return match ($role) {
            'superadmin' => '/dashboard/superadmin',
            'admin'      => '/dashboard/admin',
            'pengurus'   => '/dashboard/pengurus',
            default      => '/dashboard/warga',
        };
    }
}
