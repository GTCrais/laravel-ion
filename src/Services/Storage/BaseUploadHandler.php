<?php

namespace GTCrais\Ion\Services\Storage;

use GTCrais\Ion\Resources\EntityConfig;
use Illuminate\Http\UploadedFile;

abstract class BaseUploadHandler
{
	/**
	 * @var FilePathResolver
	 */
	protected $filePathResolver;
	protected $entityConfig;
	/**
	 * @var UploadedFile
	 */
	protected $file;
	protected $field;

	public function __construct(FilePathResolver $filePathResolver, $file, $property, $locale, EntityConfig $entityConfig)
	{
		$this->filePathResolver = $filePathResolver;
		$this->file = $file;
		$this->entityConfig = $entityConfig;
		$this->field = $entityConfig->field($property);

		$this->filePathResolver->setFieldAndLocale($this->field, $locale);
	}

	abstract public function handle(): array;
}