<?php

namespace GTCrais\Ion\Services\Storage;

use GTCrais\Ion\Resources\Field;
use Illuminate\Support\Facades\Storage;

class FilePathResolver
{
	/**
	 * @var null|Field
	 */
	protected $field;
	/**
	 * @var null|string
	 */
	protected $locale;

	public function __construct($field = null, $locale = null)
	{
		$this->setFieldAndLocale($field, $locale);
	}

	public function setFieldAndLocale($field, $locale)
	{
		$this->field = $field;
		$this->locale = $locale;

		return $this;
	}

	public function getMixedFilePath($uid, $filename = null)
	{
		return $this->getBasePathForFileType('file') . $this->getRelativeMixedFilePath($uid, $filename);
	}

	public function getMixedFileUrl($uid, $filename = null)
	{
		return $this->getBaseUrlForFileType('file') . $this->getRelativeMixedFilePath($uid, urlencode($filename));
	}

	public function getImagePath($uid, $folder = null, $filename = null)
	{
		return $this->getBasePathForFileType('image') . $this->getRelativeImagePath($uid, $folder, $filename);
	}

	public function getImageUrl($uid, $folder = null, $filename = null)
	{
		return $this->getBaseUrlForFileType('image') . $this->getRelativeImagePath($uid, $folder, urlencode($filename));
	}

	public function getRelativeMixedFilePath($uid, $filename = null)
	{
		$path = '/' . $uid;

		if (optional($this->field)->type == 'editor') {

			// For files uploaded through text editor, put each file in folder
			// designated for field property, so multiple files with the
			// same name can exist for different field properties

			$path .= '/' . $this->field->localizedProperty($this->locale);

		}

		if ($filename) {
			$path .= '/' . $filename;
		}

		return $path;
	}

	public function getRelativeImagePath($uid, $folder = null, $filename = null)
	{
		$path = '/' . $uid . '/' . $folder;

		if ($filename) {
			$path .= '/' . $filename;
		}

		return $path;
	}

	public function getImageThumbnailFolder()
	{
		return 'ion-thumbnail';
	}

	public function getDefaultImageFolder()
	{
		return 'default';
	}

	protected function getBasePathForFileType($basicFileType)
	{
		return $this->basePath() . $this->getRelativeUrlForFileType($basicFileType);
	}

	protected function getBaseUrlForFileType($basicFileType)
	{
		return $this->baseUrl() . $this->getRelativeUrlForFileType($basicFileType);
	}

	protected function getRelativeUrlForFileType($basicFileType)
	{
		if (optional($this->field)->isFile() || optional($this->field)->type == 'editor') {

			// Field is Mixed File, Image, or was uploaded through Text Editor

			if ($basicFileType == 'image') {
				return '/img/entity-images';
			}

			return '/file/entity-files';

		}

		return '/file/misc';
	}

	protected function basePath()
	{
		return '/public';
	}

	protected function baseUrl()
	{
		return rtrim(Storage::url(''), '/');
	}
}