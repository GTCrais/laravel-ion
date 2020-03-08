<?php

namespace GTCrais\Ion\Services\Storage\Concerns;

trait HandlesImages
{
	protected function getFinalTransforms()
	{
		return $this->appendThumbnailDataToTransforms($this->getTransforms());
	}

	protected function getTransforms()
	{
		return $this->field->transforms ?: $this->defaultTransforms();
	}

	protected function defaultTransforms()
	{
		return [
			[null, null, null, $this->filePathResolver->getDefaultImageFolder(), 100]
		];
	}

	protected function appendThumbnailDataToTransforms($transforms)
	{
		$transforms[] = [null, 80, 'resize', $this->filePathResolver->getImageThumbnailFolder(), 100];

		return $transforms;
	}
}