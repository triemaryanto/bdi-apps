<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FirebaseAuth
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (! session('firebase_uid')) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Unauthenticated'], 401)
                : redirect('/login');
        }

        if (session('user_status') === 'pending') {
            return redirect('/pending');
        }

        if (! empty($roles) && ! in_array(session('user_role'), $roles)) {
            abort(403, 'Akses ditolak');
        }

        return $next($request);
    }
}
