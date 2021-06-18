<?php

namespace App\Http\Middleware;

use Closure;
use Request;
use JWTAuth;
use \Symfony\Component\HttpKernel\Exception\HttpException;

class IsProvider
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if ( $currentUser->role->role == 'provider' ){
            return $next($request);
        } else {
            throw new HttpException( 403, 'Wrong user type' );
        }
    }
}
