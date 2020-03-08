<?php

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Services\Concerns\UtilizesDomainConfig;

class DomainHelper
{
	use UtilizesDomainConfig;

	public $ionWebPath;
	public $ionApiPath;

	public function __construct()
	{
		$this->fetchDomainConfig();

		$this->ionWebPath = $this->ionWebPrefix;
		$this->ionApiPath = $this->ionWebPath . '/' . $this->ionApiPrefix;
	}

}