<?php

namespace GTCrais\Ion\Providers;

use Illuminate\Support\ServiceProvider;
use GTCrais\Ion\Services\EntityConfigProvider;
use GTCrais\Ion\Services\RequestHelper;

class EntityConfigServiceProvider extends ServiceProvider
{
	public function boot(EntityConfigProvider $entityConfigProvider, RequestHelper $requestHelper)
	{
		if ($requestHelper->isIonRequest()) {
			$entityConfigProvider->buildEntityConfigs();
		}
	}
}