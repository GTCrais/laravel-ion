<?php

namespace GTCrais\Ion\Http\Controllers;

use GTCrais\Ion\Services\TokenManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GTCrais\Ion\Exceptions\TooManyAttemptsException;
use GTCrais\Ion\Services\DataProvider;
use GTCrais\Ion\Http\Controllers\Concerns\ThrottlesRequests;

class LoginController extends Controller
{
	use ThrottlesRequests;

	public function __construct()
	{
		$this->setThrottlingParameters();
	}

	public function setThrottlingParameters()
	{
		$this->maxAttempts = 6;
		$this->lockoutDuration = 1 * 60; // In seconds
	}

	/**
	 * Login from Admin.
	 *
	 * @param Request $request
	 * @param DataProvider $dataProvider
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function statefulLogin(Request $request, DataProvider $dataProvider)
	{
		try {
			$this->simpleThrottle($request);

			if (auth()->attempt($request->only('email', 'password'))) {
				return response()->json($dataProvider->getInitialData());
			}

			return response()->json('Netočan email ili lozinka', 401);
		} catch (TooManyAttemptsException $e) {
			return response()->json($e->getMessage(), 403);
		}
    }

	public function logout()
	{
		auth()->logout();

		return response()->json('Success');
	}
}
