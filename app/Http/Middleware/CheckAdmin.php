<?php

namespace App\Http\Middleware;

use App\Models\Admin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // User shall always be logged in using the IVAO SSO prior to being checked as an admin
        $user = Auth::user();
        
        if (!$user) {
            return $next($request);
        }
        
        $isAdmin = Admin::isAdmin($user->vid);
        
        if (!$isAdmin) {
            Session::put('session_toast', [
                'type' => 'error',
                'title' => 'Access Denied',
                'description' => 'You need administrator privileges to access this area.',
                'position' => 'toast-top toast-end', 
                'icon' => 'phosphor.shield-warning',
                'css' => 'alert-error',
                'timeout' => 5000,
                'redirectTo' => null
            ]);
            return redirect()->route('home');
        }
        
        return $next($request);
    }
}