<?php

namespace GTCrais\Ion\Http\Middleware;

use GTCrais\Ion\Services\RequestHelper;
use Closure;

class SetLocale
{
	protected $requestHelper;

	public function __construct(RequestHelper $requestHelper)
	{
		$this->requestHelper = $requestHelper;
	}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	if ($this->requestHelper->isIonRequest()) {
			app()->setLocale('en');
		}

        return $next($request);
    }
}
