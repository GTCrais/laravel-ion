<?php

namespace GTCrais\Ion\Http\Middleware;

use Closure;

class EnforceAjax
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
		if (!$request->ajax()) {
			abort(403, "Not an ajax request");
		}

        return $next($request);
    }
}