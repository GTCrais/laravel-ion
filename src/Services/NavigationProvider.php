<?php

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Resources\EntityConfig;

class NavigationProvider
{
	protected $entityConfigProvider;

	public function __construct(EntityConfigProvider $entityConfigProvider)
	{
		$this->entityConfigProvider = $entityConfigProvider;
	}

	public function navigationForAuthUser()
	{
		return $this->navigationForUser(auth()->user());
	}

	public function navigationForUser($user = null)
	{
		if (!$user) {
			return null;
		}

		return $this->parsedNavigation($user, config('ion.navigation'));
	}

	protected function parsedNavigation($user, $navigationConfig)
	{
		$parsedConfig = [];

		foreach ($navigationConfig as $entityUid) {

			/** @var EntityConfig $entityConfig */
			$entityConfig = $this->entityConfigProvider->getEntityConfig($entityUid);

			$parsedConfig[] = [
				'uid' => $entityUid,
				'type' => $entityConfig->type,
				'urlPath' => $entityUid,
				'name' => $entityConfig->navigationName(),
				'icon' => $entityConfig->iconClass
			];
		}

		return $parsedConfig;
	}
}