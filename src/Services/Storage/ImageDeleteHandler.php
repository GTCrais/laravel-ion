<?php

namespace GTCrais\Ion\Services\Storage;

use GTCrais\Ion\Services\Storage\Concerns\HandlesImages;
use Illuminate\Support\Facades\File;

class ImageDeleteHandler extends BaseDeleteHandler
{
	use HandlesImages;

	public function delete()
	{
		foreach ($this->getFinalTransforms() as $transformData) {
			$folder = $transformData[3];
			$path = $this->filePathResolver->getImagePath($this->entityConfig->uid, $folder, $this->filename);

			if (File::exists($path)) {
				File::delete($path);
			}
		}
    }
}