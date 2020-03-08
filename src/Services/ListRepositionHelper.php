<?php

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Resources\Entity;
use Illuminate\Http\Request;

class ListRepositionHelper
{
	protected $request;
	protected $entityConfig;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function updateObjectPosition($object)
	{
		/** @var Entity $object */
		$this->entityConfig = $object->entityConfig();
		[$direction, $adjacentObjectId] = $this->getDirectionAndAdjacentObjectId();

		/** @var Entity $model */
		$model = $this->entityConfig->fqcn;
		$newPosition = $model::findOrFail($adjacentObjectId)->{$this->entityConfig->structure('position.property')};
		$oldPosition = $object->{$this->entityConfig->structure('position.property')};

		try {
		    \DB::beginTransaction();

			$object->{$this->entityConfig->structure('position.property')} = $newPosition;
		    $object->save();

			$query = $model::where('id', '<>', $object->id);

			if ($direction == 'up') {

				$query->where('position', '>=', $newPosition)
					->where('position', '<', $oldPosition)
					->increment('position');

			} else if ($direction == 'down') {

				$query->where('position', '>', $oldPosition)
					->where('position', '<=', $newPosition)
					->decrement('position');

			}

		    \DB::commit();
		} catch (\Exception $e) {
		    \DB::rollBack();
			\Log::error($e);
		}
	}

	protected function getDirectionAndAdjacentObjectId()
	{
		$adjacentObjectId = null;
		$direction = $this->request->input('direction');

		$adjacentObjectId = ($direction == 'up') ?
			$this->request->input('below_object_id') :
			$this->request->input('above_object_id');

		if ($this->entityConfig->structure('direction') == 'desc') {
			$direction = ($direction == 'up') ? 'down' : 'up';
		}

		return [$direction, $adjacentObjectId];
	}
}