<?php

namespace GTCrais\Ion\Services\Concerns;

trait UtilizesDomainConfig
{
	public $ionWebPrefix;
	public $ionApiPrefix;

	protected function fetchDomainConfig()
	{
		$this->ionWebPrefix = config('ion.settings.routePrefix');
		$this->ionApiPrefix = config('ion.settings.apiRoutePrefix');
	}
}