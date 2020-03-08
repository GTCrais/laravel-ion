<?php

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Resources\EntityConfig;

class EntityConfigProvider
{
	protected $entityConfigBuilder;
	protected $entityFqcnUidMap = [];
	public $entityConfigs = [];

	public function __construct(EntityConfigBuilder $entityConfigBuilder)
	{
		$this->entityConfigBuilder = $entityConfigBuilder;
	}

	public function buildEntityConfigs()
	{
		$entityConfigArrays = config('ion.entities') ?: [];

		foreach ($entityConfigArrays as $entityConfigArray) {
			$entityConfig = $this->entityConfigBuilder->build($entityConfigArray);
			$this->entityConfigs[$entityConfig->uid] = $entityConfig;
			$this->entityFqcnUidMap[$entityConfig->fqcn] = $entityConfig->uid;
		}

		$this->entityConfigBuilder->assignParentEntityDataToEntityConfigs($this->entityConfigs);
	}

	public function getEntityConfigOrNull($uid)
	{
		return $this->getEntityConfig($uid, true);
	}

	/**
	 * @param $uid
	 * @param bool $returnNullIfNotFound
	 *
	 * @return EntityConfig|null
	 * @throws \Exception
	 */
	public function getEntityConfig($uid, $returnNullIfNotFound = false)
	{
		try {
		    return $this->entityConfigs[$uid];
		} catch (\Exception $e) {
			if ($returnNullIfNotFound) {
				return null;
			}

			throw new \Exception('Entity Config for UID ' . $uid . ' doesn\'t exist!', $e->getCode());
		}
	}

	public function getEntityConfigByFqcn($fqcn)
	{
		try {
		    return $this->getEntityConfig($this->entityFqcnUidMap[$fqcn]);
		} catch (\Exception $e) {
			throw new \Exception('Entity Config for FQCN ' . $fqcn . ' doesn\'t exist!', $e->getCode());
		}
	}

	public function entityConfigsForAuthUser()
	{
		return $this->entityConfigsForUser(auth()->user());
	}

	public function entityConfigsForUser($user = null)
	{
		if (!$user) {
			return null;
		}

		return $this->entityConfigs;
	}
}