<?php

namespace GTCrais\Ion\Exceptions;


class InvalidDataException extends \Exception
{
	/**
	 * Render the exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return \Illuminate\Http\Response
	 */
	public function render($request)
	{
		if ($request->wantsJson()) {
			return response()->json($this->getMessage(), 422);
		}

		abort(422, $this->getMessage());
	}
}