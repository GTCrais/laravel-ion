<?php

namespace GTCrais\Ion\Http\Controllers;

use GTCrais\Ion\Services\DataProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebController extends Controller
{
	public function __invoke(Request $request, DataProvider $dataProvider)
	{
		return view('ion::layouts.default')->with($dataProvider->getConstData());
	}
}