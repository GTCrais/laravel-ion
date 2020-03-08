<?php

namespace GTCrais\Ion\Http\Controllers;

use App\Http\Controllers\Controller;
use GTCrais\Ion\Http\Controllers\Concerns\ValidatesExistence;
use GTCrais\Ion\Resources\Entity;
use GTCrais\Ion\Resources\Field;
use GTCrais\Ion\Services\EntityConfigProvider;
use GTCrais\Ion\Services\Storage\BaseDeleteHandler;
use GTCrais\Ion\Services\Storage\BaseUploadHandler;
use GTCrais\Ion\Services\Storage\MixedFileDeleteHandler;
use GTCrais\Ion\Services\Storage\MixedFileUploadHandler;
use GTCrais\Ion\Services\Storage\ImageDeleteHandler;
use GTCrais\Ion\Services\Storage\ImageUploadHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
	use ValidatesExistence;

	protected $request;
	protected $entityConfigProvider;
	protected $entityConfig;
	protected $object;

	public function __construct(EntityConfigProvider $entityConfigProvider)
	{
		$this->entityConfigProvider = $entityConfigProvider;
	}

	/**
	 * @param Request $request
	 * @param $uid
	 *
	 * @return JsonResponse
	 * @throws ValidationException
	 * @throws \Exception
	 */
	public function store(Request $request, $uid)
	{
		$this->init($request, $uid);

		$this->validate($request, [
			'file' => $this->storeValidationRules(),
			'property' => 'required'
		]);

		$data = $this->fileUploadHandler()->handle();

		$this->optionallyUpdateFilenameOnObject($data['filename']);

		return response()->json($data);
    }

	/**
	 * @param Request $request
	 * @param $uid
	 *
	 * @return JsonResponse
	 * @throws ValidationException
	 */
	public function delete(Request $request, $uid)
	{
		$this->init($request, $uid);

		$this->validate($request, [
			'filename' => 'required',
			'property' => 'required'
		]);

		$this->fileDeleteHandler()->delete();
		$this->optionallyUpdateFilenameOnObject(null);

		return response()->json();
    }

	protected function optionallyUpdateFilenameOnObject($filename)
	{
		// If Form Field is not File, that means the file was either uploaded
		// through a Text Editor or through some other means, so there's
		// no need to update the form field Property on the Object
		if ($this->formField()->isFile()) {
			optional($this->object)->update([
				$this->formField()->localizedProperty($this->request->input('locale')) => $filename
			]);
		}
    }

	protected function init(Request $request, $uid)
	{
		$this->request = $request;
		$this->entityConfig = $this->entityConfigProvider->getEntityConfig($uid);
		/** @var Entity $entity */
		$entity = $this->entityConfig->fqcn;
		$object = null;

		if ($id = $this->request->input('id')) {
			$object = $this->validateExistence($entity::find($id) ?: $entity, $id);
		}

		$this->object = $object;
    }

	/**
	 * @return BaseUploadHandler
	 */
	protected function fileUploadHandler()
	{
		$class =
			$this->formField()->isImage() ?
			ImageUploadHandler::class :
			MixedFileUploadHandler::class;

		return app()->makeWith($class, [
			'file' => $this->request->file('file'),
			'property' => $this->request->input('property'),
			'locale' => $this->request->input('locale'),
			'entityConfig' => $this->entityConfig,
		]);
    }

	/**
	 * @return BaseDeleteHandler
	 */
	protected function fileDeleteHandler()
	{
		$class =
			$this->formField()->isImage() ?
				ImageDeleteHandler::class :
				MixedFileDeleteHandler::class;

		return app()->makeWith($class, [
			'filename' => $this->request->input('filename'),
			'property' => $this->request->input('property'),
			'locale' => $this->request->input('locale'),
			'entityConfig' => $this->entityConfig,
		]);
    }

	/**
	 * @return Field
	 */
	protected function formField()
	{
		return $this->entityConfig->field($this->request->input('property'));
    }

	protected function storeValidationRules()
	{
		/** @var Field $field */
		$field = $this->entityConfig->field($this->request->input('property'));

		if ($uploadRules = $field->uploadRules) {
			return $uploadRules;
		}

		if ($field->isImage()) {
			return 'image';
		}

		return '';
    }
}
