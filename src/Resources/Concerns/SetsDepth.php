<?php

namespace GTCrais\Ion\Resources\Concerns;

use GTCrais\Ion\Resources\Entity;

/** @mixin Entity */

trait SetsDepth
{
	protected function setDepth()
	{
		$this->{$this->entityConfig()->structure('depth.property')} = $this->getDepth();

		return $this;
	}

	protected function getDepth()
	{
		/** @var Entity $model */
		$model = $this->entityConfig()->fqcn;
		$depthProperty = $this->entityConfig()->structure('depth.property');
		$parent = $model::select($depthProperty)
			->where(
				$this->getKeyName(),
				$this->{$this->entityConfig()->structure('parentProperty')}
			)->first();

		return $parent ? $parent->{$depthProperty} + 1 : 0;
	}
}