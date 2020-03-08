<?php

namespace GTCrais\Ion\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\ModelNotFoundException;

trait ValidatesExistence
{
	protected function validateExistence($objectOrClass, $id)
	{
		if (is_object($objectOrClass)) {
			return $objectOrClass;
		}

		throw (new ModelNotFoundException())->setModel(
			$objectOrClass, $id
		);
	}
}