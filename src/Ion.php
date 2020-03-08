<?php

namespace GTCrais\Ion;

use Illuminate\Support\Str;

class Ion
{
	public static $styles = [];
	public static $localScripts = [];
	public static $remoteScripts = [];

	public static function script($path)
	{
		if (Str::startsWith($path, ['http', 'https'])) {
			static::$remoteScripts[] = $path;
		} else {
			static::$localScripts[md5($path)] = $path;
		}
	}

	public static function style($path)
	{
		static::$styles[md5($path)] = $path;
	}
}