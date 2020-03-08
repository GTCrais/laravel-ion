<?php

namespace GTCrais\Ion\Services;

use Illuminate\Http\Request;
use GTCrais\Ion\Resources\EntityConfig;

/*** NOTE: THIS CLASS IS NOT USED ANYWHERE ***/
// todo: delete this class if it ends up not being used

class InputFilter
{
	protected $request;
	protected $arrayHelper;

	public function __construct(Request $request, ArrayHelper $arrayHelper)
	{
		$this->request = $request;
		$this->arrayHelper = $arrayHelper;
	}

	public function filteredInput(EntityConfig $entityConfig)
	{
		$input = $this->request->all();

		$allowedFields = $entityConfig->fields->all;

		foreach ($entityConfig->parentEntitiesData as $parentData) {
			if (!isset($allowedFields[$parentData->parentProperty])) {
				$allowedFields[$parentData->parentProperty] = $this->arrayHelper->arrayToObject([
					'property' => $parentData->parentProperty
				]);
			}
		}

		foreach ($input as $property => $value) {
			if (!array_key_exists($property, $allowedFields)) {
				unset ($input[$property]);
			}
		}

		return $input;
	}
}