<?php

namespace GTCrais\Ion\Http\Controllers;

use App\Http\Controllers\Controller;
use GTCrais\Ion\Events\EntityObjectStored;
use GTCrais\Ion\Events\EntityObjectUpdated;
use GTCrais\Ion\Exceptions\InvalidDataException;
use GTCrais\Ion\Services\SelectManyRelationshipSynchronizer;
use Illuminate\Http\Request;
use GTCrais\Ion\Repositories\EntityRepository;
use GTCrais\Ion\Services\DataProvider;
use GTCrais\Ion\Services\EntityConfigProvider;
use GTCrais\Ion\Services\ValidationHelper;
use GTCrais\Ion\Resources\EntityConfig;
use GTCrais\Ion\Resources\Entity;

class EntityController extends Controller
{
	protected $dataProvider;
	protected $entityConfigProvider;
	protected $entityRepository;
	protected $validationHelper;
	/**
	 * @var SelectManyRelationshipSynchronizer
	 */
	protected $selectManyRelationshipSynchronizer;

	public function __construct(
		DataProvider $dataProvider,
		EntityConfigProvider $entityConfigProvider,
		EntityRepository $entityRepository,
		ValidationHelper $validationHelper,
		SelectManyRelationshipSynchronizer $selectManyRelationshipSynchronizer
	)
	{
		$this->dataProvider = $dataProvider;
		$this->entityConfigProvider = $entityConfigProvider;
		$this->entityRepository = $entityRepository;
		$this->validationHelper = $validationHelper;
		$this->selectManyRelationshipSynchronizer = $selectManyRelationshipSynchronizer;
	}

	public function index(Request $request, $entityUid)
	{
		$this->authorizeAction($entityUid, 'index');

		return response()->json($this->dataProvider->getEntityData($entityUid));
	}

	public function create($entityUid)
	{
		$this->authorizeAction($entityUid, 'create');

		return response()->json($this->dataProvider->getEntityObjectData($entityUid));
	}

	public function edit($entityUid, $id)
	{
		$responseData = $this->dataProvider->getEntityObjectData($entityUid, $id);

		$this->authorizeAction($entityUid, 'edit', $responseData['object']);

		return response()->json($responseData);
    }

	public function store(Request $request, $entityUid)
	{
		$this->authorizeAction($entityUid, 'store');

		/** @var EntityConfig $entityConfig */
		$entityConfig = $this->entityConfigProvider->getEntityConfig($entityUid);
		/** @var Entity $model */
		$model = $entityConfig->fqcn;
		$filteredInput = $this->validateRequest($request->all(), $entityConfig);

		try {
		    \DB::beginTransaction();

			/** @var Entity $object */
			$object = ($model::create($filteredInput))
				->postActionMutations($request, $filteredInput)
				->postAddMutations($request, $filteredInput)
				->conditionallySetPropertiesOnAdd();

			$this->selectManyRelationshipSynchronizer->sync($object, $filteredInput);

			event(new EntityObjectStored($object, $filteredInput));

			\DB::commit();
		} catch (\Exception $e) {
			\DB::rollBack();
			throw($e);
		}

		// todo: maybe reload the object via DataProvider so we can get exactly the same object as if we were editing it

		return response()->json(compact('object'));
	}

	public function update(Request $request, $entityUid, $id)
	{
		/** @var EntityConfig $entityConfig */
		$entityConfig = $this->entityConfigProvider->getEntityConfig($entityUid);
		/** @var Entity $entity */
		$entity = $entityConfig->fqcn;
		/** @var Entity $object */
		$object = $entity::without($entity::editWithout())->findOrFail($id);

		$this->authorizeAction($entityUid, 'update', $object);

		$originalObject = clone $object;
		$filteredInput = $this->validateRequest($request->all(), $entityConfig, $object);

		try {
		    \DB::beginTransaction();

			/** @var Entity $object */
			tap($object)->update($filteredInput)
				->postActionMutations($request, $filteredInput, $originalObject)
				->postEditMutations($request, $filteredInput, $originalObject)
				->conditionallySetPropertiesOnEdit();

			$this->selectManyRelationshipSynchronizer->sync($object, $filteredInput);

			// todo: reset object's position if parent was changed

			event(new EntityObjectUpdated($object, $originalObject, $filteredInput));

		    \DB::commit();
		} catch (\Exception $e) {
		    \DB::rollBack();
			throw($e);
		}

		// todo: maybe reload the object via DataProvider so we can get exactly the same object as if we were editing it

		return response()->json(compact('object'));
	}

	public function updateKeyValue(Request $request, $entityUid)
	{
		$this->authorizeAction($entityUid, 'updateKeyValue');

		/** @var EntityConfig $entityConfig */
		$entityConfig = $this->entityConfigProvider->getEntityConfig($entityUid);

		if ($entityConfig->structure('type') != 'keyValue') {
			return response()->json('Entity Config is not of \'keyValue\' type.', 422);
		}

		$filteredInput = $this->validateRequest($request->all(), $entityConfig);

		try {
			\DB::beginTransaction();

			/** @var \Eloquent $fqcn */
			$fqcn = $entityConfig->fqcn;

			foreach ($fqcn::all() as $object) {
				if (array_key_exists($object->key, $filteredInput)) {
					$object->update(['value' => $filteredInput[$object->key]]);
				}
			}

			\DB::commit();
		} catch (\Exception $e) {
			\DB::rollBack();
			throw($e);
		}

		return response()->json(['entityData' => $this->dataProvider->getEntityData($entityUid)]);
	}

	public function destroy($entityUid, $id)
	{
		/** @var EntityConfig $entityConfig */
		$entityConfig = $this->entityConfigProvider->getEntityConfig($entityUid);
		/** @var Entity $entity */
		$entity = $entityConfig->fqcn;
		/** @var \Eloquent $object */
		$object = $entity::findOrFail($id);

		$this->authorizeAction($entityUid, 'destroy', $object);

		$object->delete();

		return response()->json($this->entityRepository->getIndexData($entityConfig));
	}

	/**
	 * @param $entityUid
	 * @param $action
	 * @param null $object
	 *
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 * @throws \Exception
	 */
	protected function authorizeAction($entityUid, $action, $object = null)
	{
		$entityConfig = $this->entityConfigProvider->getEntityConfigOrNull($entityUid);

		if (\Gate::getPolicyFor($entity = optional($entityConfig)->fqcn)) {
			$this->authorize($action, $object ?? $entity);
		}
	}

	protected function validateRequest(array $input, EntityConfig $entityConfig, $object = null)
	{
		$validator = \Validator::make(
			$input,
			$this->validationHelper->validationRules($entityConfig, $object),
			[],
			$entityConfig->validationAttributes
		);

		$this->validateTreeStructureParent($input, $entityConfig, $object);

		return $validator->validate();
	}

	protected function validateTreeStructureParent(array $input, EntityConfig $entityConfig, $object = null)
	{
		$parentProperty = $entityConfig->structure('parentProperty');
		$depthProperty = $entityConfig->structure('depth.property');
		$maxDepth = $entityConfig->structure('depth.max');
		/** @var Entity $entity */
		$entity = $entityConfig->fqcn;

		// todo: make same-depth parent restriction optional

		if (
			$entityConfig->structure('type') == 'tree' &&
			$depthProperty &&
			array_key_exists($parentProperty, $input)
		) {
			if ($object) {

				// Make sure the new parent has the
				// same depth as the old parent

				if ($object->{$parentProperty}) {
					$oldParent = $entity::findOrFail($object->{$parentProperty});
					$newParent = $entity::findOrFail($input[$parentProperty]);

					if ($oldParent->{$depthProperty} != $newParent->{$depthProperty}) {
						throw new InvalidDataException("Incorrect parent depth");
					}
				} else if ($input[$parentProperty]) {
					throw new InvalidDataException("Parent ID for this object must be NULL");
				}
			} else if ($maxDepth && $input[$parentProperty]) {
				$newParent = $entity::findOrFail($input[$parentProperty]);

				if ($newParent->{$depthProperty} > ($maxDepth - 1)) {
					throw new InvalidDataException("Incorrect parent depth");
				}
			}
		}
	}
}
