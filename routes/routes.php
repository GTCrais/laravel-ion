<?php

use GTCrais\Ion\Http\Middleware\SetLocale;
use GTCrais\Ion\Http\Middleware\EnforceAjax;
use GTCrais\Ion\Services\DomainHelper;

/** @var DomainHelper $domainHelper */
$domainHelper = app(DomainHelper::class);

Route::prefix($domainHelper->ionApiPath)
	->middleware(['web', SetLocale::class, EnforceAjax::class])
	->namespace('GTCrais\Ion\Http\Controllers')
	->group(function() {

		Route::post('login')->name('login')->uses('LoginController@statefulLogin');
		Route::get('get-initial-data')->name('getInitialData')->uses('DataController@getInitialData');
		Route::get('get-csrf')->name('getCsrf')->uses('DataController@getCsrf');

		Route::middleware(config('ion.settings.authorizedApiRoutesMiddleware'))->group(function() {

			Route::post('logout')->name('logout')->uses('LoginController@logout');
			Route::get('{entityUid}')->name('entity')->uses('EntityController@index');
			Route::get('{entityUid}/add')->name('entityCreate')->uses('EntityController@create');
			Route::post('{entityUid}/add')->name('entityStore')->uses('EntityController@store');
			Route::put('{entityUid}/edit')->name('entityUpdateKeyValue')->uses('EntityController@updateKeyValue');
			Route::get('{entityUid}/edit/{id}')->name('entityEdit')->uses('EntityController@edit');
			Route::put('{entityUid}/edit/{id}')->name('entityUpdate')->uses('EntityController@update');
			Route::delete('{entityUid}/delete/{id}')->name('entityDestroy')->uses('EntityController@destroy');
			Route::patch('{entityUid}/reposition/{id}')->name('listEntityReposition')->uses('ListRepositionController@reposition');
			Route::patch('{entityUid}/tree-reposition/{id}')->name('treeEntityReposition')->uses('TreeRepositionController@reposition');
			Route::post('{entityUid}/upload-file')->name('uploadFile')->uses('FileController@store');
			Route::delete('{entityUid}/delete-file')->name('deleteFile')->uses('FileController@delete');

		});

	});

Route::prefix($domainHelper->ionWebPath)
	->middleware(['web', SetLocale::class])
	->namespace('GTCrais\Ion\Http\Controllers')
	->group(function() {

		Route::prefix('system')
			->middleware(config('ion.settings.systemWebRoutesMiddleware'))
			->group(function() {

				Route::get('migrate-database')->name('migrateDatabase')->uses('SystemController@migrateDatabase');
				Route::get('link-storage')->name('linkStorage')->uses('SystemController@linkStorage');

			});

		Route::get('style/{key}')->name('style')->uses('AssetController@loadStyle');
		Route::get('script/{key}')->name('script')->uses('AssetController@loadScript');
		Route::get('{segments?}')->name('admin')->uses('WebController')->where('segments', '.*');

	});