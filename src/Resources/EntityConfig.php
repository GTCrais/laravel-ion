<?php

namespace GTCrais\Ion\Resources;

use GTCrais\Ion\Concerns\GetsDistantValue;

class EntityConfig extends BaseClass
{
	use GetsDistantValue;

	protected $entityTable;
	protected $entityKey;
	public $fields = [];
	public $allowedOrder = [];
	public $validationAttributes = [];
	public $relatedEntitiesData = [];
	public $parentEntitiesData = [];

	public function getEntityTable()
	{
		if (is_null($this->entityTable)) {
			$fqcn = $this->fqcn;
			$this->entityTable = (new $fqcn)->getTable();
		}

		return $this->entityTable;
	}

	public function getEntityKey()
	{
		if (is_null($this->entityKey)) {
			$fqcn = $this->fqcn;
			$this->entityKey = (new $fqcn)->getKeyName();
		}

		return $this->entityKey;
	}

	public function navigationName()
	{
		return $this->navigationName ?? $this->pluralName;
	}

	public function regularFieldOrNull($property)
	{
		return $this->regularField($property, true);
	}

	public function regularField($property, $returnNullIfNotFound = false)
	{
		return $this->field($property, 'regular', $returnNullIfNotFound);
	}

	public function translatableFieldOrNull($property)
	{
		return $this->translatableField($property, true);
	}

	public function translatableField($property, $returnNullIfNotFound = false)
	{
		return $this->field($property, 'translatable', $returnNullIfNotFound);
	}

	public function localizedFieldOrNull($property)
	{
		return $this->localizedField($property, true);
	}

	public function localizedField($property, $returnNullIfNotFound = false)
	{
		return $this->field($property, 'localized', $returnNullIfNotFound);
	}

	public function quickSearchFieldOrNull($property)
	{
		return $this->quickSearchField($property, true);
	}

	public function quickSearchField($property, $returnNullIfNotFound = false)
	{
		return $this->field($property, 'quickSearch', $returnNullIfNotFound);
	}

	public function fileFieldOrNull($property)
	{
		return $this->fileField($property, true);
	}

	public function fileField($property, $returnNullIfNotFound = false)
	{
		return $this->field($property, 'file', $returnNullIfNotFound);
	}

	public function fieldOrNull($property)
	{
		return $this->field($property, 'all', true);
	}

	/**
	 * @param $property
	 * @param string $type
	 * @param bool $returnNullIfNotFound
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function field($property, $type = 'all', $returnNullIfNotFound = false)
	{
		if (!in_array($type, ['all', 'regular', 'translatable', 'localized', 'quickSearch', 'file'])) {
			throw new \Exception('Incorrect field type');
		}

		if (!isset($this->fields->{$type}[$property]) && $returnNullIfNotFound) {
			return null;
		}

		return $this->fields->{$type}[$property];
	}

	/**
	 * @param Field $field
	 * @param Entity $object
	 *
	 * @return array|null
	 * @throws \Exception
	 */
	public function getEditValueForField($field, $object)
	{
		if ($field->autofill === false) {
			return null;
		}

		if ($this->structure('type') == 'keyValue') {

			// Even if Field is translatable, it doesn't matter for keyValue
			// type because the object's key is already localized and
			// we just need to fetch the value

			// todo: implement 'selectMany' for keyValue Entity

			return [$object->key => $object->value];

		} else if ($field->translatable) {
			$editValues = [];

			foreach (config('ion.settings.entityLocales') as $locale) {
				$editValues[$field->localizedProperty($locale)] = $object->localizedPropertyValue($field->property, $locale);
			}

			return $editValues;
		} else {
			if ($field->type == 'selectMany') {
				$value = $object->{$field->select->relationMethod}()->pluck((new $field->select->class)->getKeyName());
			} else {
				$value = $object->{$field->property};
			}

			return [$field->property => $value];
		}
	}

	public function getSelectListForField($field)
	{
		if ($field->type == 'selectMany' || $field->select->type == 'model') {
			$class = $field->select->class;
		} else if ($field->select->type == 'list') {
			$class = $this->fqcn;
		}

		$method = $field->select->listMethod;

		return app()->makeWith(SelectList::class, ['list' => $class::$method()]);
	}

	public function getSearchTableForField($field)
	{
		$fqcn = $this->fieldIsModelSelect($field) ?
			$field->select->modelName :
			$this->fqcn;

		return (new $fqcn)->getTable();
	}

	public function getQuickSearchPropertiesForField($field)
	{
		return is_array($field->quickSearch) ? $field->quickSearch : [$field->property];
	}

	/**
	 * @param $key
	 *
	 * @return int|string|null
	 */
	public function structure($key)
	{
		// todo: validate structure is set

		return $this->distantValue('structure.' . $key);
	}

	protected function fieldIsModelSelect($field)
	{
		return ($field->type == 'select' && $field->select->type == 'model');
	}
}