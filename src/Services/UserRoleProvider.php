<?php

namespace GTCrais\Ion\Services;

class UserRoleProvider
{
	protected $user;

	public function getRole($user = null)
	{
		$this->user = $user;

		return $this->getUser()->role;
	}

	protected function getUser()
	{
		return $this->user ?? auth()->user();
	}
}