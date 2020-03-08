<?php

namespace GTCrais\Ion\Services\Storage;

use Illuminate\Support\Str;

class MixedFileUploadHandler extends BaseUploadHandler
{
	public function handle(): array
	{
		$filename = $this->filename();
		$folderPath = $this->filePathResolver->getMixedFilePath($this->entityConfig->uid);

		$this->file->storePubliclyAs($folderPath, $filename);

		return [
			'filename' => $filename,
			'fileUrl' => $this->filePathResolver->getMixedFileUrl($this->entityConfig->uid, $filename),
		];
	}

	protected function filename()
	{
		return Str::slug(pathinfo($this->file->getClientOriginalName())['filename']) . '.' . $this->file->getClientOriginalExtension();
	}
}