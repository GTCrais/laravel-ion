<?php

namespace GTCrais\Ion\Concerns;

trait GetsDistantValue
{
	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function distantValue($key)
	{
		$keys = explode('.', $key);
		$value = $this;

		foreach ($keys as $_key) {
			if (is_object($value)) {
				$value = $value->{$_key};
			} else {
				break;
			}
		}

		return $value;
	}
}