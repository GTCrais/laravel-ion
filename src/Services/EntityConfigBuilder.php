<?php

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Resources\BaseClass;
use GTCrais\Ion\Resources\EntityConfig;
use GTCrais\Ion\Resources\Field;
use Illuminate\Support\Collection;

class EntityConfigBuilder
{
	protected $arrayHelper;
	protected $entityConfig;

	public function __construct(ArrayHelper $arrayHelper)
	{
		$this->arrayHelper = $arrayHelper;
	}

	public function build(array $entityConfigArray)
	{
		$fields = null;

		if (isset($entityConfigArray['fields'])) {
			$fields = $entityConfigArray['fields'];

			unset($entityConfigArray['fields']);
		}

		$this->entityConfig = $this->arrayHelper->arrayToObject($entityConfigArray, EntityConfig::class);
		$this->entityConfig->fields = $this->parseFields($fields);
		$this->entityConfig->relatedEntitiesData = $this->relatedEntitiesData();

		return $this->entityConfig;
	}

	public function assignParentEntityDataToEntityConfigs($entityConfigs)
	{
		foreach ($entityConfigs as $entityConfig) {
			foreach ($entityConfig->relatedEntitiesData as $relatedEntityUid => $relatedEntityData) {

				// Replace Related Entity's UID with its Parent Entity UID

				$relatedEntityParentData = clone $relatedEntityData;
				$relatedEntityParentData->uid = $entityConfig->uid;

				$entityConfigs[$relatedEntityUid]->parentEntitiesData[$entityConfig->uid] = $relatedEntityParentData;
			}
		}
	}

	protected function parseFields($fields)
	{
		$parsedFields = app(BaseClass::class);
		$parsedFields->all = [];
		$parsedFields->index = [];
		$parsedFields->regular = [];
		$parsedFields->translatable = [];
		$parsedFields->localized = [];
		$parsedFields->quickSearch = [];
		$parsedFields->file = [];
		$parsedFields->selectMany = [];

		if ($fields) {
			foreach ($fields as $field) {
				/** @var Field $parsedField */
				$parsedField = $this->parseField($field);

				$this->updateAllowedOrder($parsedField);
				$this->addValidationAttribute($parsedField);

				$parsedFields->all[$parsedField->property] = $parsedField;

				if ($parsedField->index) {
					$parsedFields->index[$parsedField->property] = $parsedField;
				}

				if ($parsedField->quickSearch) {
					$parsedFields->quickSearch[$parsedField->property] = $parsedField;
				}

				if ($parsedField->type == 'file') {
					$parsedFields->file[$parsedField->property] = $parsedField;
				}

				if ($parsedField->type == 'selectMany') {
					$parsedFields->selectMany[$parsedField->property] = $parsedField;
				}

				if ($parsedField->translatable) {
					$parsedFields->translatable[$parsedField->property] = $parsedField;

					foreach (config('ion.settings.entityLocales') as $locale) {
						$parsedFields->localized[$parsedField->localizedProperty($locale)] = $parsedField;
					}

				} else {
					$parsedFields->regular[$parsedField->property] = $parsedField;
				}
			}

			// Sort Index fields:
			// 1) Separate index fields into 'ordered' and 'other' groups
			// 2) FlatMap the new array with 'other' group appended to SORTED 'ordered' group
			$parsedFields->index =
				collect($parsedFields->index)->reduce(function($carry, $field) {
					$carry[!is_null(optional($field->index)->order) ? 'ordered' : 'other']->push($field);

					return $carry;
				}, collect(['ordered' => collect(), 'other' => collect()]))
					->flatMap(function($fields, $key) {
						if ($key == 'ordered') {
							/** @var Collection $fields */
							return $fields->sortBy(function($field) {
								return $field->index->order;
							});
						}

						return $fields;
					})->toArray();
		}

		return $parsedFields;
	}

	protected function parseField(array $field)
	{
		$parsedField = ['_originalField' => $field] + $field;

		return $this->arrayHelper->arrayToObject($parsedField, Field::class);
	}

	/**
	 * @param Field $field
	 */
	protected function addValidationAttribute($field)
	{
		if ($field->translatable) {
			foreach (config('ion.settings.entityLocales') as $locale) {
				$this->entityConfig->validationAttributes[$field->localizedProperty($locale)] = $field->label;
			}
		} else {
			$this->entityConfig->validationAttributes[$field->property] = $field->label;
		}
	}

	protected function updateAllowedOrder($field)
	{
		if ($field->order) {
			$this->entityConfig->allowedOrder[] = $field->property;
		}
	}

	protected function relatedEntitiesData()
	{
		$relatedEntitiesData = [];

		if ($this->entityConfig->structure('type') == 'tree') {

			// "$this->entityConfig->uid" is both Related Entity UID and
			// Parent Entity UID since the model is related to itself
			// because of tree structure

			$relatedEntitiesData[$this->entityConfig->uid] = $this->arrayHelper->arrayToObject([
				'uid' => $this->entityConfig->uid,
				'parentProperty' => $this->entityConfig->structure('parentProperty'),
				'parentConfig' => [
					'structureType' => $this->entityConfig->structure('type')
				]
			]);
		}

		if ($this->entityConfig->relatedEntities) {
			foreach ($this->entityConfig->relatedEntities as $relatedEntityData) {
				$relatedEntitiesData[$relatedEntityData->uid] = $this->arrayHelper->arrayToObject([
					'uid' => $relatedEntityData->uid,
					'parentProperty' => $relatedEntityData->parentProperty,
					'parentConfig' => [
						'structureType' => $this->entityConfig->structure('type')
					]
				]);
			}
		}

		return $relatedEntitiesData;
	}
}