<?php

namespace JavoByte\Logviewer;

use Illuminate\Support\ServiceProvider;

class LogviewerServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('JavoByte\Logviewer\LogReader', function($app){
			return new LogReader(storage_path().'/logs');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

	public function boot()
	{

		$this->publishes([
			__DIR__.'/../../resources/assets/javobyte' => public_path('javobyte'),
		], 'assets');

		$this->loadViewsFrom(__DIR__.'/../../resources/views', 'logviewer');
	}

}
