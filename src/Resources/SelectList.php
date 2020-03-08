<?php

namespace GTCrais\Ion\Resources;


class SelectList implements \JsonSerializable
{
	protected $list;

	public function __construct($list)
	{
		$this->list = collect($list)->map(function($value, $key) {
			return [
				'key' => $key,
				'value' => $value
			];
		})->values();
	}

	public function jsonSerialize()
	{
		return $this->list;
	}
}