<?php

namespace GTCrais\Ion\Services\Storage;

use GTCrais\Ion\Services\Storage\Concerns\HandlesImages;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as InterventionImage;

class ImageUploadHandler extends BaseUploadHandler
{
	use HandlesImages;

	protected $allowedTransformMethods = ['fit', 'crop', 'resize', 'resizeCanvas', null];
	protected $validConstraints = [
		'fit' => ['upsize'],
		'resize' => ['upsize', 'aspectRatio']
	];
	protected $defaultConstraints = [
		'resize' => ['aspectRatio']
	];

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function handle(): array
	{
		$this->validateImageDimensions();
		$this->validateTransformMethods($this->getTransforms());

		$filename = $this->generateFilename();

		$this->processImageTransforms($filename);

		return [
			'filename' => $filename,
			'thumbnailUrl' => $this->filePathResolver->getImageUrl($this->entityConfig->uid, $this->filePathResolver->getImageThumbnailFolder(), $filename)
		];
	}

	protected function processImageTransforms($name)
	{
		foreach ($this->getFinalTransforms() as $transformData) {
			$image = Image::make($this->file);

			$width = $transformData[0];
			$height = $transformData[1];
			$transformMethod = $transformData[2];
			$folder = $transformData[3];
			$quality = $transformData[4];
			$constraints = $transformData[5] ?? null;

			if ($transformMethod == 'fit') {

				$image->fit($width, $height, function($constraint) use ($transformMethod, $constraints) {
					$this->setConstraints($constraint, $constraints, $transformMethod);
				})->encode(null, $quality);

			} else if ($transformMethod == 'crop') {

				$image->crop($width, $height)->encode(null, $quality);

			} else if ($transformMethod == 'resize') {

				$image->resize($width, $height, function($constraint) use ($transformMethod, $constraints) {
					$this->setConstraints($constraint, $constraints, $transformMethod);
				})->encode(null, $quality);

			} else if ($transformMethod == 'resizeCanvas') {

				$image->resize($width, null, function($constraint) {
					/** @var Constraint $constraint */
					$constraint->aspectRatio();
					$constraint->upsize();
				});

				$image->resize(null, $height, function($constraint) {
					/** @var Constraint $constraint */
					$constraint->aspectRatio();
					$constraint->upsize();
				});

				if ($image->width() < $width || $image->height() < $height) {
					$image = Image::canvas($width, $height, '#FFFFFF')->insert($image, 'center');
				}

				$image->encode(null, $quality);

			} else if (is_null($transformMethod)) {

				$image->encode(null, $quality);

			}

			$path = $this->filePathResolver->getImagePath($this->entityConfig->uid, $folder, $name);

			Storage::put($path, $image, 'public');
		}
	}

	protected function setConstraints(Constraint $interventionConstraint, $constraints, $transformMethod)
	{
		// Make sure Valid Constraints exist for this Transform Method
		if (in_array($transformMethod, array_keys($this->validConstraints))) {
			if (is_array($constraints)) {

				// Iterate over the user-passed constraints, and if they
				// are valid for this transform method, apply them
				foreach ($constraints as $constraint) {
					if (in_array($constraint, $this->validConstraints[$transformMethod])) {
						$interventionConstraint->{$constraint}();
					}
				}
			} else {

				// Apply default constraints
				foreach (($this->defaultConstraints[$transformMethod] ?? []) as $constraint) {
					$interventionConstraint->{$constraint}();
				}
			}
		}
	}

	protected function validateTransformMethods($transforms)
	{
		foreach ($transforms as $transformData) {
			if (!in_array($transformData[2], $this->allowedTransformMethods)) {
				throw new \Exception('Image transform method not allowed: ' . $transformData[2] . ' Allowed methods: ' . implode(', ', $this->allowedTransformMethods));
			}
		}
	}

	protected function generateFilename()
	{
		$ext = $this->file->getClientOriginalExtension();
		$folder = $this->getTransforms()[0][3];

		do {
			$name = Str::random(32) . '.' . $ext;
		} while (\File::exists($this->filePathResolver->getImagePath($this->entityConfig->uid, $folder, $name)));

		return $name;
	}

	protected function validateImageDimensions()
	{
		/** @var InterventionImage $image */
		$image = Image::make($this->file);

		if (
			$this->field->minimumDimensions &&
			(
				$image->width() < $this->field->minimumDimensions->width ||
				$image->height() < $this->field->minimumDimensions->height
			)
		) {
			$minWidth = $this->field->minimumDimensions->width ?: 1;
			$minHeight = $this->field->minimumDimensions->height ?: 1;

			abort(422, 'Minimum image dimensions are ' . $minWidth . ' x ' . $minHeight . 'px');
		}
	}
}