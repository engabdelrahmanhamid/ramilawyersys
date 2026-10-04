<?php


namespace App\Http\Middleware;


use Closure;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\App;
use Auth;

class BranchMiddleware extends Middleware

{
    use \App\Traits\ApiResponseTrait;

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */

    public function handle($request, Closure $next, ...$guards)
    {
        App::setLocale($request->header('lang'));
        $admin = Auth::user();
        // Only super admins may look at another branch (or all branches);
        // everyone else is always limited to their own branch, whatever the request asks for.
        if ($admin && (int) $admin->super !== 1)
            $request['branch_id'] = $admin->branch_id;
        elseif (!$request->branch_id)
            $request['branch_id'] = null;
        return $next($request);

    }

}



