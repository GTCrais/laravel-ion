<?php

namespace GTCrais\Ion\Resources\Concerns;

use GTCrais\Ion\Resources\Entity;
use GTCrais\Ion\Resources\Field;
use Illuminate\Support\Str;

/** @mixin Entity */

trait SetsSlug
{
	protected function setSlug($action)
	{
		/** @var Field $field */
		if ($field = $this->entityConfig()->translatableFieldOrNull($this->entityConfig()->structure('slug.from'))) {
			foreach (config('ion.settings.entityLocales') as $locale) {

				// todo: generate localized slug and set localized slug property value using a custom Service

				$this->{$this->localizedSlugProperty($locale)} = $this->getSlugForLocale($action, $locale);
			}
		} else {
			$this->{$this->localizedSlugProperty()} = $this->getSlug($action);
		}

		return $this;
	}

	protected function localizedSlugProperty($locale = null)
	{
		$property = $this->entityConfig()->structure('slug.property');

		return $locale ? $property . '_' . $locale : $property;
	}

	protected function localizedSlugFrom($locale = null)
	{
		$property = $this->entityConfig()->structure('slug.from');

		return $locale ? $property . '_' . $locale : $property;
	}

	protected function getSlugForLocale($action, $locale)
	{
		return $this->getSlug($action, $locale);
	}

	protected function getSlug($action, $locale = null)
	{
		if (
			$this->entityConfig()->structure('type') == 'tree' &&
			$this->entityConfig()->structure('slug.skipForBaseDepth') &&
			$this->{$this->entityConfig()->structure('parentProperty')} == null
		) {
			return null;
		}

		$slug = $this->generateSlug($locale);
		$suffix = 0;

		while($this->entityWithSlugExists($action, $slug, $locale)) {
			$suffix++;
			$slug = $this->generateSlug($locale, $suffix);
		}

		return $slug;
	}

	protected function generateSlug($locale, $suffix = null)
	{
		$slug = Str::slug(strip_tags($this->{$this->localizedSlugFrom($locale)}));
		$slug = $suffix ? $slug . '-' . $suffix : $slug;

		return $this->optionallyPrependParentSlug($slug, $locale);
	}

	protected function optionallyPrependParentSlug($slug, $locale)
	{
		if ($this->entityConfig()->structure('type') == 'tree') {
			$parentProperty = $this->entityConfig()->structure('parentProperty');

			if ($this->{$parentProperty}) {
				$parentSlug = static::where($this->getKeyName(), $this->{$parentProperty})
					->first()
					->{$this->localizedSlugProperty($locale)};

				$slug = $parentSlug ? ($parentSlug . '/' . $slug) : $slug;
			}
		}

		return $slug;
	}

	protected function entityWithSlugExists($action, $slug, $locale = null)
	{
		$query = static::where($this->localizedSlugProperty($locale), $slug);

		if ($this->entityConfig()->structure('type') == 'tree') {
			$parentProperty = $this->entityConfig()->structure('parentProperty');
			$query = $query->where($parentProperty, $this->{$parentProperty});
		}

		if ($action == 'edit') {
			$query = $query->where('id', '!=', $this->id);
		}

		return !!$query->count(['id']);
	}
}