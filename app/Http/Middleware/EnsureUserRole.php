<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $currentRole = $request->user()?->role ?? $request->session()->get('account_role');

        $normal = fn ($role) => strtolower((string) $role);
        if (!$currentRole || !in_array($normal($currentRole), array_map($normal, $roles), true)) {
            return redirect()
                ->route('login')
                ->withErrors(['role' => 'Your account is not allowed to access that page yet.']);
        }

        return $next($request);
    }
}
