<?php

namespace GTCrais\Ion\Http\Controllers;

use App\Http\Controllers\Controller;
use GTCrais\Ion\Ion;

class AssetController extends Controller
{
	public function loadScript($key)
	{
		$path = Ion::$localScripts[$key] ?? null;

		if (!$path) {
			abort(404);
		}

		return response($this->responseContentForPath($path), 200, ['Content-Type' => 'application/javascript']);
	}

	public function loadStyle($key)
	{
		$path = Ion::$styles[$key] ?? null;

		if (!$path) {
			abort(404);
		}

		return response($this->responseContentForPath($path), 200, ['Content-Type' => 'text/css']);
	}

	protected function responseContentForPath($path)
	{
		try {
		    return file_get_contents($path);
		} catch (\Throwable $e) {
			abort(404, $e->getMessage());
		}
	}
}
