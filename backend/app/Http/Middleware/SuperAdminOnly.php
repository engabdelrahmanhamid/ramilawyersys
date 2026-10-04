<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Actions that change who can access the system or how money is numbered
 * (employees, branches, settings, payment methods) are limited to super admins
 * on the server, not only hidden in the interface.
 * Responds with HTTP 200 + status 0 like the rest of this API, so the front-end shows the message instead of logging out.
 */
class SuperAdminOnly
{
    public function handle($request, Closure $next)
    {
        $admin = Auth::user();
        if (!$admin || (int) $admin->super !== 1) {
            $message = $request->header('lang') == 'en'
                ? 'you do not have permission to do this'
                : 'ليس لديك صلاحية لتنفيذ هذا الاجراء';
            return response()->json(['status' => 0, 'message' => $message, 'data' => null], 200);
        }
        return $next($request);
    }
}
