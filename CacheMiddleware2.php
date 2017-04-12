<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Cache;

class CachePage
{
    public function handle($request, Closure $next)
    {
        $key = $request->url();  //key for caching/retrieving the response value

        if (Cache::has($key))  //If it's cached...
            return Cache::get($key);   //... return the value from cache and we're done.

        $response = $next($request);  //If it wasn't cached, execute the request and grab the response

        $cachingTime = 60;  //Let's cache it for 60 minutes
        Cache::put($key, $response->getContent(), $cachingTime);  //Cache response

        return $response;
    }
}