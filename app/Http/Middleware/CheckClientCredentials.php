<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckClientCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
      try {
          $user = auth()->userOrFail();
          if(empty($user)) {
            $response = setResponseData(array(), false, 406, 'Unauthorized.');
            return response()->json($response, 406);
          }
      } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\UserNotDefinedException $e) {
        $response = setResponseData(array(), false, 406, 'Unauthorized.');
        return response()->json($response, 406);
      }

      return $next($request);
    }
}
