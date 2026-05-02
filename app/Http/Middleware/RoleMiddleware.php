<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, $roles)) {
            $user = Auth::user();
            
            if (in_array($user->role, ['admin', 'owner', 'mekanik', 'kasir'])) {
                return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut. Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.');
            }

            return redirect('/');
        }

        return $next($request);
    }
}
