<?php

namespace GTCrais\Ion\Resources;

use GTCrais\Ion\Services\Storage\FilePathResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use GTCrais\Ion\Services\EntityConfigProvider;
use Illuminate\Support\Collection;

/** @mixin \Eloquent */

trait Entity
{
	protected $entityConfig;
	protected $filePathResolver;

	public function related()
	{
		return [];
	}

	public static function getAdditionalIndexData()
	{
		return [];
	}

	public static function getAdditionalAddData()
	{
		return [];
	}

	public static function getAdditionalEditData($object)
	{
		return [];
	}

	public static function getNewEntity()
	{
		return new static;
	}

	protected function getDisplayValuesAttribute()
	{
		if ($this->entityConfig()->structure('type') == 'keyValue') {
			return $this->getKeyValueDisplayValues();
		} else {
			return $this->getStandardDisplayValues();
		}
	}

	protected function getFileFieldsDataAttribute()
	{
		$data = [];

		foreach ($this->entityConfig()->fields->file as $fileField) {
			/** @var Field $fileField */
			if ($fileField->translatable) {
				foreach (config('ion.settings.entityLocales') as $locale) {
					$this->setDataForFileField($data, $fileField, $locale);
				}
			} else {
				$this->setDataForFileField($data, $fileField);
			}
		}

		return $data;
	}

	protected function setDataForFileField(&$data, $fileField, $locale = null)
	{
		/** @var Field $fileField */
		$localizedProperty = $fileField->localizedProperty($locale);
		$data[$localizedProperty]['filename'] = $this->{$localizedProperty};

		if ($fileField->isImage()) {
			$data[$localizedProperty]['thumbnailUrl'] = $this->getImageThumbnail($fileField, $locale);
		} else {
			$data[$localizedProperty]['url'] = $this->getMixedFileUrl($fileField, $locale);
		}
	}

	protected function getImageThumbnail($fileField, $locale = null)
	{
		/** @var Field $fileField */
		return $this->filePathResolver()
			->setFieldAndLocale($fileField, $locale)
			->getImageUrl(
				$this->entityConfig()->uid,
				$this->filePathResolver()->getImageThumbnailFolder(),
				$this->{$fileField->localizedProperty($locale)}
			);
	}

	protected function getMixedFileUrl($fileField, $locale = null)
	{
		/** @var Field $fileField */
		return $this->filePathResolver()
			->setFieldAndLocale($fileField, $locale)
			->getMixedFileUrl(
				$this->entityConfig()->uid,
				$this->{$fileField->localizedProperty($locale)}
			);
	}

	protected function getAllowedActionsAttribute()
	{
		// todo: implement these on the frontend. See if it's possible to use Policy to determine these values

		return [
			'edit' => true,
			'view' => true,
			'delete' => true,
			'addChild' => true,
		];
	}

	public static function addActionAllowed()
	{
		return true;
	}

	/**
	 * @param $property
	 * @param null $locale
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function localizedPropertyValue($property, $locale = null)
	{
		// todo: make fetching localized property value customizable via custom Translation service
		/** @var Field $field */
		$field = $this->entityConfig()->field($property);

		return $this->{$field->localizedProperty($locale)};
	}

	protected function getStandardDisplayValues()
	{
		$values = collect();
		$fqcn = $this->entityConfig()->fqcn;
		$hidden = (new $fqcn)->getHidden();

		foreach ($this->entityConfig()->fields->all as $field) {
			if (!in_array($field->property, $hidden)) {
				$this->pushValueForFieldProperty($values, $field);
			}
		}

		// Push the value for 'representedBy' into Display Values
		$representedBy = $this->entityConfig()->representedBy;

		if (!$this->entityConfig()->fieldOrNull($representedBy)) {
			$values->put($representedBy, $this->{$representedBy});
		}

		return $values;
	}

	protected function getKeyValueDisplayValues()
	{
		$values = collect();

		// todo: we should probably check for localized field here as well, same as when fetching default edit values

		if ($field = $this->entityConfig()->fieldOrNull($this->key)) {
			$this->pushValueForFieldProperty($values, $field);
		}

		return $values;
	}

	/**
	 * @param Collection $values
	 * @param Field $field
	 *
	 * @throws \Exception
	 */
	protected function pushValueForFieldProperty(Collection $values, $field)
	{
		if ($field->translatable) {
			$locales = config('ion.settings.entityLocales');

			foreach ($locales as $locale) {
				$values->put(
					$field->localizedProperty($locale),
					$this->getDisplayValueForField($field, $locale)
				);
			}

			// todo: figure out why we push the default locale display value for unlocalized property?
			$values->put(
				$field->property,
				$this->getDisplayValueForField($field, $locales[0])
			);
		} else {
			$values->put($field->property, $this->getDisplayValueForField($field));
		}
	}

	/**
	 * @param Field $field
	 * @param null $locale
	 *
	 * @return mixed|null
	 * @throws \Exception
	 */
	protected function getDisplayValueForField($field, $locale = null)
	{
		if ($field->displayValue) {
			if ($field->displayValue->type == 'model') {
				$method = $field->displayValue->method;
				$property = $field->displayValue->property;

				return $this->$method->localizedPropertyValue($property, $locale) ?? null;
			} else if ($field->displayValue->type == 'accessor') {
				return $this->{$field->displayValue->method};
			}
		} else {
			// todo: adapt KeyValue model for localization

			return $this->entityConfig()->structure('type') == 'keyValue' ?
				$this->value :
				$this->localizedPropertyValue($field->property, $locale);
		}

		return null;
	}

	/**
	 * @return EntityConfig
	 */
	public function entityConfig()
	{
		if (is_null($this->entityConfig)) {
			$this->entityConfig = app(EntityConfigProvider::class)->getEntityConfigByFqcn(static::class);
		}

		return $this->entityConfig;
	}

	/**
	 * @return FilePathResolver
	 */
	public function filePathResolver()
	{
		if (is_null($this->filePathResolver)) {
			$this->filePathResolver = app(FilePathResolver::class);
		}

		return $this->filePathResolver;
	}

	public static function addEntityQuickSearchParameters(Builder $query, $term)
	{
		return $query;
	}

	public static function indexWith()
	{
		return [];
	}

	public static function indexWithout()
	{
		return [];
	}

	public static function editWith()
	{
		return [];
	}

	public static function editWithout()
	{
		return [];
	}

	public function scopeIndexQuery(Builder $query)
	{
		return $query;
	}

	public function postActionMutations(Request $request, $filteredInput, $originalObject = null)
	{
		return $this;
	}

	public function postAddMutations(Request $request, $filteredInput)
	{
		return $this;
	}

	public function postEditMutations(Request $request, $filteredInput, $originalObject)
	{
		return $this;
	}

	public function conditionallySetPropertiesOnAdd()
	{
		$this->conditionallySetPosition()
			->conditionallySetSlug('add')
			->conditionallySetDepth()
			->save();

		return $this;
	}

	public function conditionallySetPropertiesOnEdit()
	{
		$this->conditionallySetSlug('edit')
			->save();

		return $this;
	}

	public function conditionallySetSlug($action)
	{
		if ($this->entityConfig()->structure('slug')) {
			return $this->setSlug($action);
		}

		return $this;
	}

	public function conditionallySetPosition()
	{
		if ($this->entityConfig()->structure('position')) {
			return $this->setPosition();
		}

		return $this;
	}

	public function conditionallySetDepth()
	{
		if ($this->entityConfig()->structure('depth.property')) {
			return $this->setDepth();
		}

		return $this;
	}

	public function toArray()
	{
		$this->append(['display_values', 'allowed_actions', 'file_fields_data']);

		return parent::toArray();
	}
}