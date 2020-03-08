<?php

namespace GTCrais\Ion\Http\Controllers;

use GTCrais\Ion\Resources\Entity;
use GTCrais\Ion\Services\EntityConfigProvider;
use GTCrais\Ion\Services\TreeRepositionHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TreeRepositionController extends Controller
{
	protected $request;
	protected $entityConfigProvider;
	protected $entityConfig;
	protected $repositionHelper;

	public function __construct(EntityConfigProvider $entityConfigProvider, TreeRepositionHelper $repositionHelper)
	{
		$this->entityConfigProvider = $entityConfigProvider;
		$this->repositionHelper = $repositionHelper;
	}

	public function reposition(Request $request, $entityUid, $id)
	{
		$this->request = $request;
		$this->entityConfig = $this->entityConfigProvider->getEntityConfig($entityUid);
		/** @var Entity $model */
		$model = $this->entityConfig->fqcn;
		$object = $model::setEagerLoads([])->findOrFail($id);

		$this->validate($request, [
			'below_object_id' => 'present',
			'above_object_id' => 'present',
			'parent_id' => 'nullable|exists:' . $this->entityConfig->getEntityTable() . ',' . $this->entityConfig->getEntityKey()
		]);

		$this->validateParentDepth($object);

		try {
		    \DB::beginTransaction();

			$this->repositionHelper->setObjectPosition($object);
			$object->{$this->entityConfig->structure('parentProperty')} = $this->request->input('parent_id');

			$object->save();
			$object->refresh();

			\DB::commit();
		} catch (\Exception $e) {
			\DB::rollBack();

			throw $e;
		}

		return response()->json(compact('object'));
    }

	protected function validateParentDepth($object)
	{
		$parentProperty = $this->entityConfig->structure('parentProperty');

		if (
			($depthProperty = $this->entityConfig->structure('depth.property')) &&
			($parentId = $this->request->input('parent_id')) != $object->{$parentProperty}
		) {
			if ($parentId) {
				/** @var Entity $model */
				$model = $this->entityConfig->fqcn;
				$newParent = $model::setEagerLoads([])->findOrFail($parentId);
				$oldParent = $model::setEagerLoads([])->findOrFail($object->{$parentProperty});

				if ($newParent->{$depthProperty} != $oldParent->{$depthProperty}) {
					abort(422, "Invalid new parent object depth.");
				}
			}
		}
    }
}
