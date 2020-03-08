<?php

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Resources\Entity;
use Illuminate\Http\Request;

class TreeRepositionHelper
{
	protected $request;
	protected $entityConfig;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function setObjectPosition($object)
	{
		/** @var Entity $object */
		$this->entityConfig = $object->entityConfig();
		$positionProperty = $this->entityConfig->structure('position.property');
		[$aboveObject, $belowObject] = $this->getAdjacentObjects();

		if (!$belowObject) {
			$position = number_format(microtime(true), 3, "", "");
		} else if (!$aboveObject) {
			$position = $belowObject->{$positionProperty} - 1;

			if ($position < 1) {
				$position = 1;
				$this->incrementPositionsGreaterOrEqualTo($position);
			}
		} else {
			// Both Above and Below Objects exist
			$position = $aboveObject->{$positionProperty} + 1;

			if ($belowObject->{$positionProperty} == $position) {
				$this->incrementPositionsGreaterOrEqualTo($position);
			}
		}

		$object->{$positionProperty} = $position;
	}

	protected function incrementPositionsGreaterOrEqualTo($position)
	{
		/** @var Entity $fqcn */
		$fqcn = $this->entityConfig->fqcn;
		$positionProperty = $this->entityConfig->structure('position.property');
		$parentProperty = $this->entityConfig->structure('parentProperty');

		$fqcn::where($positionProperty, '>=', $position)
			->where($parentProperty, $this->request->input('parent_id'))
			->increment($positionProperty);
	}

	protected function getAdjacentObjects()
	{
		/** @var Entity $fqcn */
		$fqcn = $this->entityConfig->fqcn;
		$aboveObjectId = $this->request->input('above_object_id');
		$belowObjectId = $this->request->input('below_object_id');

		return [
			$aboveObjectId ? $fqcn::setEagerLoads([])->find($aboveObjectId) : null,
			$belowObjectId ? $fqcn::setEagerLoads([])->find($belowObjectId) : null
		];
	}
}