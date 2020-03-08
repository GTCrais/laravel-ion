<?php

namespace GTCrais\Ion\Resources\Concerns;

use GTCrais\Ion\Resources\Entity;

/** @mixin Entity */

trait SetsPosition
{
	/**
	 * @return $this
	 * @throws \Exception
	 */
	protected function setPosition()
	{
		$this->{$this->entityConfig()->structure('position.property')} = $this->getNextPosition();

		return $this;
	}

	protected function getNextPosition()
	{
		if ($this->entityConfig()->structure('type') == 'list') {
			return $this->getNextPositionForListStructure();
		}

		if ($this->entityConfig()->structure('type') == 'tree') {
			return $this->getNextPositionForTreeStructure();
		}

		throw new \Exception('Could not get next position for structure type: ' . $this->entityConfig()->structure('type'));
	}

	// todo: make position provider

	protected function getNextPositionForListStructure()
	{
		return number_format(microtime(true), 3, "", "");
	}

	protected function getNextPositionForTreeStructure()
	{
		return number_format(microtime(true), 3, "", "");
	}
}