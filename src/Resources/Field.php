<?php

namespace GTCrais\Ion\Resources;

use GTCrais\Ion\Concerns\GetsDistantValue;

class Field extends BaseClass
{
	use GetsDistantValue;

	public function localizedProperty($locale = null)
	{
		if ($this->translatable && $locale) {
			return $this->property . '_' . $locale;
		}

		return $this->property;
	}

	public function isFile()
	{
		return $this->isMixedFile() || $this->isImage();
	}

	public function isMixedFile()
	{
		return $this->basicFileType() == 'file';
	}

	public function isImage()
	{
		return $this->basicFileType() == 'image';
	}

	public function basicFileType()
	{
		if ($this->type == 'file') {
			return $this->imageFile ? 'image' : 'file';
		}

		return null;
	}
}