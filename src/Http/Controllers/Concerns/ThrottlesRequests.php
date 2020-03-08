<?php

namespace GTCrais\Ion\Http\Controllers\Concerns;

use GTCrais\Ion\Exceptions\TooManyAttemptsException;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

trait ThrottlesRequests
{
	protected $maxAttempts = 5;
	protected $lockoutDuration = 1; // In minutes
	protected $rateLimiter;

	/**
	 * @param Request $request
	 *
	 * @throws TooManyAttemptsException
	 */
	protected function simpleThrottle(Request $request)
	{
		if ($this->hasTooManyAttempts($request, $this->maxAttempts)) {
			$errorMessage = trans('auth.throttle', ['seconds' => $this->availableIn($this->throttleKey($request))]);

			throw new TooManyAttemptsException($errorMessage);
		}

		$this->incrementAttempts($request, $this->lockoutDuration);
	}

	protected function hasTooManyAttempts($requestOrKey, $maxAttempts)
	{
		if ($requestOrKey instanceof Request) {
			$key = $this->throttleKey($requestOrKey);
		} else {
			$key = $requestOrKey;
		}

		return $this->limiter()->tooManyAttempts(
			$key, $maxAttempts
		);
	}

	protected function incrementAttempts($requestOrKey, $lockoutDuration)
	{
		if ($requestOrKey instanceof Request) {
			$key = $this->throttleKey($requestOrKey);
		} else {
			$key = $requestOrKey;
		}

		$this->limiter()->hit($key, $lockoutDuration);
	}

	protected function throttleKey(Request $request, $url = false, $append = "")
	{
		if (!$url) {
			$url = $request->url();
		}

		$url = rtrim($url, "/");

		return $url . "_" . $request->ip() . $append;
	}

	protected function resetAttempts($key)
	{
		$this->limiter()->resetAttempts($key);
	}

	protected function clear($key)
	{
		$this->limiter()->clear($key);
	}

	protected function retriesLeft($key, $maxAttempts)
	{
		$retriesLeft = $this->limiter()->retriesLeft($key, $maxAttempts);

		if ($retriesLeft <= 0) {
			$retriesLeft = 0;
		}

		return $retriesLeft;
	}

	protected function availableIn($key)
	{
		return $this->limiter()->availableIn($key);
	}

	protected function lock($key, $maxAttempts, $lockoutDuration)
	{
		$retriesLeft = $this->retriesLeft($key, $maxAttempts);

		if ($retriesLeft) {
			for ($i = 1; $i <= $retriesLeft; $i++) {
				$this->limiter()->hit($key, $lockoutDuration);
			}
		}
	}

	/**
	 * @return RateLimiter;
	 */
	protected function limiter()
	{
		if (is_null($this->rateLimiter)) {
			$this->rateLimiter = app(RateLimiter::class);
		}

		return $this->rateLimiter;
	}
}