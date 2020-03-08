<?php

namespace GTCrais\Ion\Services;

use Illuminate\Support\Arr;
use GTCrais\Ion\Resources\BaseClass;

class ArrayHelper
{
	public function objectToArray($object)
	{
		// todo: fix so Rule class can be used
		return json_decode(json_encode($object), true);
	}

	public function arrayToObject($arrayOrValue, $objectClass = null)
	{
		if (is_array($arrayOrValue)) {
			$object = $objectClass ? app($objectClass) : app(BaseClass::class);

			foreach ($arrayOrValue as $key => $value) {
				if (is_array($value) && Arr::isAssoc($value)) {
					$object->$key = $this->arrayToObject($value);
				} else {
					$object->$key = $value;
				}
			}

			return $object;
		}

		// It's an actual value
		return $arrayOrValue;
	}
}