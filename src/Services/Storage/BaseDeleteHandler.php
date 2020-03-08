<?php

namespace GTCrais\Ion\Services\Storage;

use GTCrais\Ion\Resources\EntityConfig;

abstract class BaseDeleteHandler
{
	/**
	 * @var FilePathResolver
	 */
	protected $filePathResolver;
	protected $entityConfig;
	protected $filename;
	protected $field;

	public function __construct(FilePathResolver $filePathResolver, $filename, $property, $locale, EntityConfig $entityConfig)
	{
		$this->filePathResolver = $filePathResolver;
		$this->entityConfig = $entityConfig;
		$this->filename = $filename;
		$this->field = $entityConfig->field($property);

		$this->filePathResolver->setFieldAndLocale($this->field, $locale);
	}

	abstract public function delete();
}