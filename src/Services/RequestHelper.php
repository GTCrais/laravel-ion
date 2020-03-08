<?php

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Services\Concerns\UtilizesDomainConfig;
use Illuminate\Http\Request;

class RequestHelper
{
	use UtilizesDomainConfig;

	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->fetchDomainConfig();
	}

	public function isWebRequest()
	{
		if ($this->isIonRequest()) {
			return false;
		}

		return true;
	}

	public function isIonRequest()
	{
		return $this->isIonWebRequest() || $this->isIonApiRequest();
	}

	public function isIonWebRequest()
	{
		if ($this->ionWebPrefix) {
			if (request()->segment(1) == $this->ionWebPrefix && request()->segment(2) != $this->ionApiPrefix) {
				return true;
			}
		}

		if (request()->segment(1) != $this->ionApiPrefix) {
			return true;
		}

		return false;
	}

	public function isIonApiRequest()
	{
		if ($this->ionWebPrefix) {
			if (request()->segment(1) == $this->ionWebPrefix && request()->segment(2) == $this->ionApiPrefix) {
				return true;
			}
		}

		if (request()->segment(1) == $this->ionApiPrefix) {
			return true;
		}

		return false;
	}
}