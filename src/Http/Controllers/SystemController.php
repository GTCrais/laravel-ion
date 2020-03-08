<?php

namespace GTCrais\Ion\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemController extends Controller
{
	public function migrateDatabase()
	{
		\Artisan::call('migrate', ['--force' => true]);

		return redirect()->route('admin');
	}

	public function linkStorage()
	{
		\Artisan::call('storage:link');

		return redirect()->route('admin');
	}
}
