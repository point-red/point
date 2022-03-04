<?php

namespace App\Http\Middleware;

use Closure;

class LogRequest
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
        log_object(json_encode($request->headers->all()).'----'.json_encode($request->all()));
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $start = LARAVEL_START;
        $end = microtime(true);
        $diff = $end - $start;

        log_object('['.$request->method().'] '.$request->url().' Response::'.json_encode($response->getContent()).' '.$diff);
    }
}
