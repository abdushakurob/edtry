<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!in_array($user->role, $roles)) {
            return response()->view('errors.403', [
                'message' => 'You do not have permission to access this page.'
            ], 403);
        }

        return $next($request);
    }
}
