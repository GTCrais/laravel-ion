<?php

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Resources\EntityConfig;
use GTCrais\Ion\Resources\Field;

class ValidationHelper
{
	protected $entityConfigProvider;
	protected $arrayHelper;

	public function __construct(EntityConfigProvider $entityConfigProvider, ArrayHelper $arrayHelper)
	{
		$this->entityConfigProvider = $entityConfigProvider;
		$this->arrayHelper = $arrayHelper;
	}

	public function validationRules(EntityConfig $entityConfig, $object = null)
	{
		$rules = [];

		foreach ($entityConfig->fields->regular as $field) {
			$rules[$field->property] = $this->parseRules($field->validationRules, $object);
		}

		/** @var Field $field */
		foreach ($entityConfig->fields->translatable as $field) {
			$fieldRules = $this->parseRules($field->validationRules, $object);

			foreach (config('ion.settings.entityLocales') as $locale) {
				$rules[$field->localizedProperty($locale)] = $fieldRules;
			}
		}

		if ($entityConfig->additionalValidationRules) {
			$rules += $this->parseRules($entityConfig->additionalValidationRules, $object);
		}

		return $this->appendParentEntityRules($entityConfig, $rules, $object);
	}

	protected function appendParentEntityRules(EntityConfig $entityConfig, $rules, $object)
	{
		foreach ($entityConfig->parentEntitiesData as $parentData) {
			if (!isset($rules[$parentData->parentProperty])) {
				$rules[$parentData->parentProperty] = [
					'nullable',
					'exists:' . $entityConfig->getEntityTable() . ',' . $entityConfig->getEntityKey()
				];

				if ($parentData->parentConfig->structureType == 'tree') {
					// todo: some additional validation if depth may not be changed
				}
			}
		}

		return $rules;
	}

	protected function parseRules($rules, $object)
	{
		// This will return rules as pipe separated string, or an array
		$rules = $this->arrayHelper->objectToArray($rules);

		if (is_array($rules)) {
			if ($object) {

				// If the rules are separated into 'add' and 'edit' rules,
				// take the 'edit' rules because this is an edit action

				$rules = $this->rulesToArray(
					isset($rules['add']) || isset($rules['edit']) ?
						($rules['edit'] ?? '') :
						$rules
				);
			} else {

				// If the rules are separated into 'add' and 'edit' rules,
				// take the 'add' rules because this is an add action

				$rules = $this->rulesToArray(
					isset($rules['add']) || isset($rules['edit']) ?
						($rules['add'] ?? '') :
						$rules
				);
			}
		} else {
			$rules = $this->rulesToArray($rules);
		}

		if ($object) {
			$rules = $this->replaceRulePlaceholders($rules, $object);
		}

		return $rules;
	}

	protected function rulesToArray($fieldRules)
	{
		return is_array($fieldRules) ? $fieldRules : explode("|", $fieldRules);
	}

	protected function replaceRulePlaceholders($fieldRules, $object)
	{
		foreach ($fieldRules as &$fieldRule) {
			$fieldRule = str_replace('{objectId}', $object->id, $fieldRule);
		}

		return $fieldRules;
	}
}