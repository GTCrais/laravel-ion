<?php

namespace GTCrais\Ion\Services;

class HttpMethod
{
	protected $httpMethod;

	public function get($key = null, $default = null)
	{
		return is_null($key) ? $_GET : ($_GET[$key] ?? $default);
	}

	public function post($key = null, $default = null)
	{
		return is_null($key) ? $_POST : ($_POST[$key] ?? $default);
	}

	public function input($key = null, $default = null)
	{
		$this->validateHttpMethod($this->httpMethod);

		$value = $this->{$this->httpMethod}($key, $default);
		$this->nullHttpMethod();

		return $value;
	}

	public function setHttpMethod($method)
	{
		$this->validateHttpMethod($method);
		$this->httpMethod = $method;

		return $this;
	}

	protected function validateHttpMethod($method)
	{
		if (!in_array($method, ['get', 'post'])) {
			throw new \Exception("Incorrect HTTP method. HTTP method must be 'get' or 'post'.");
		}
	}

	protected function nullHttpMethod()
	{
		$this->httpMethod = null;
	}
}