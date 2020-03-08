<?php

namespace GTCrais\Ion\Http\Controllers;

use GTCrais\Ion\Resources\Entity;
use GTCrais\Ion\Services\EntityConfigProvider;
use GTCrais\Ion\Services\ListRepositionHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ListRepositionController extends Controller
{
	protected $entityConfigProvider;
	protected $repositionHelper;

	public function __construct(EntityConfigProvider $entityConfigProvider, ListRepositionHelper $repositionHelper)
	{
		$this->entityConfigProvider = $entityConfigProvider;
		$this->repositionHelper = $repositionHelper;
	}

	public function reposition(Request $request, $entityUid, $id)
	{
		$this->validate($request, [
			'below_object_id' => 'present',
			'above_object_id' => 'present',
			'direction' => 'in:up,down'
		]);

		$entityConfig = $this->entityConfigProvider->getEntityConfig($entityUid);
		/** @var Entity $model */
		$model = $entityConfig->fqcn;
		$object = $model::findOrFail($id);

		$this->repositionHelper->updateObjectPosition($object);

		return response()->json();
    }
}
