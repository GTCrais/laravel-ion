<?php

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Resources\Entity;
use GTCrais\Ion\Resources\Field;

class SelectManyRelationshipSynchronizer
{
	public function sync($object, $filteredInput)
	{
		/** @var Field $field */
		/** @var Entity $object */
		foreach ($object->entityConfig()->fields->selectMany as $field) {
			if (array_key_exists($field->property, $filteredInput)) {
				$relatedData = [];

				if (is_array($filteredInput[$field->property])) {
					foreach ($filteredInput[$field->property] as $counter => $relatedId) {
						if ($positionProperty = $field->distantValue('select.position.property')) {
							$relatedData[$relatedId] = [$positionProperty => $counter + 1];
						} else {
							$relatedData[] = $relatedId;
						}
					}
				}

				$object->{$field->select->relationMethod}()->sync($relatedData);
			}
		}
	}
}