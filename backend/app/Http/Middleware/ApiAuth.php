<?php


namespace App\Http\Middleware;


use Closure;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\App;
use Auth;

class ApiAuth extends Middleware

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
        try {
            $response = $next($request);
            if (isset($response->exception) && $response->exception)
                throw $response->exception;
            return $response;
        } catch (\Exception $e) {
            $code = $e instanceof AuthenticationException ? 401 : 500;
            return $this->apiResponseMessage(0,$e->getMessage(),$code);
        }
    }

}



