<?php

namespace GTCrais\Ion\Http\Middleware;

use Closure;
use GTCrais\Ion\Services\UserRoleProvider;

class AuthorizeUser
{
	/**
	 * @var UserRoleProvider
	 */
	protected $userRoleProvider;

	public function __construct(UserRoleProvider $userRoleProvider)
	{
		$this->userRoleProvider = $userRoleProvider;
	}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$userRoles)
    {
		if (auth()->guest()) {
			abort(401);
		}

		if ($userRoles && !in_array($this->userRoleProvider->getRole(), $userRoles)) {
			abort(401);
		}

        return $next($request);
    }
}
