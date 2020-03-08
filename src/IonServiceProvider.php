<?php

namespace GTCrais\Ion;

use GTCrais\Ion\Console\Commands\Install;
use GTCrais\Ion\Providers\EntityConfigServiceProvider;
use GTCrais\Ion\Services\DomainHelper;
use GTCrais\Ion\Services\EntityConfigProvider;
use GTCrais\Ion\Services\HttpMethod;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Tests\TestCase;

class IonServiceProvider extends ServiceProvider
{
	public $singletons = [
		DomainHelper::class => DomainHelper::class,
		EntityConfigProvider::class => EntityConfigProvider::class,
		HttpMethod::class => HttpMethod::class
	];

	protected $isInstalled;

	public function register()
	{
		$this->commands([
			Install::class
		]);

		if ($this->isInstalled()) {
			app()->register(EntityConfigServiceProvider::class);
			app('router')->aliasMiddleware(config('ion.settings.ionAuthorizeMiddlewareAlias'), config('ion.settings.ionAuthorizeMiddlewareClass'));
		}
	}

	public function boot()
	{
		$this->registerPublishables();

		if ($this->isInstalled()) {
			$this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
			$this->loadViewsFrom(__DIR__ . '/../resources/views', 'ion');
			$this->registerIonAlias();
			$this->setRequestMacros();
		}
	}

	protected function registerPublishables()
	{
		$this->publishes([
			__DIR__ . '/../dist/css' => public_path('vendor/ion/css'),
			__DIR__ . '/../dist/js' => public_path('vendor/ion/js')
		], ['ion', 'ion-assets']);

		$this->publishes([
			__DIR__ . '/../dist/fonts' => public_path('vendor/ion/fonts'),
			__DIR__ . '/../dist/img' => public_path('vendor/ion/img')
		], ['ion', 'ion-images-and-fonts']);

		$this->publishes([
			__DIR__ . '/../resources/views' => resource_path('views/vendor/ion'),
		], ['ion-views']);
	}

	protected function registerIonAlias()
	{
		AliasLoader::getInstance()->alias(config('ion.settings.ionClassAlias'), Ion::class);
	}

	protected function setRequestMacros()
	{
		// todo: move this to RequestMacroServiceProvider

		Request::macro('methodGet', function() {
			return app(HttpMethod::class)->setHttpMethod('get');
		});

		Request::macro('methodPost', function() {
			return app(HttpMethod::class)->setHttpMethod('post');
		});
	}

	protected function isInstalled()
	{
		if (is_null($this->isInstalled)) {
			$this->isInstalled = is_file(config_path('ion/settings.php'));
		}

		return $this->isInstalled;
	}
}