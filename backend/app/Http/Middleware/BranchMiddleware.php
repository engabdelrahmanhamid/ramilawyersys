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
        if ($request->branch_id)
            $request['branch_id'] = $request->branch_id;
        else{
            if ($admin)
                $request['branch_id'] = $admin->super == 1 ? null : $admin->branch_id;
    }
        return $next($request);

    }

}



