<?php

namespace GTCrais\Ion\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GTCrais\Ion\Services\DataProvider;

class DataController extends Controller
{
	protected $dataProvider;

	public function __construct(DataProvider $dataProvider)
	{
		$this->dataProvider = $dataProvider;
	}

	public function getInitialData()
	{
		return response()->json($this->dataProvider->getInitialData());
    }

	public function getCsrf()
	{
		return response()->json(['csrf' => csrf_token()]);
	}
}
