<?php namespace Cartalyst\Extensions;
/**
 * Part of the Extensions package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Extensions
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Extensions\Extension;
use Illuminate\Support\ServiceProvider;

class ExtensionsServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('cartalyst/extensions', 'cartalyst/extensions');

		// We told a little lie in the configuration. Extensions are actually
		// auto-registered upon booting of the Extensions Service Provider as
		// we had no access to configuration
		if ($this->app['config']['cartalyst/extensions::auto_register'])
		{
			Extension::setConnectionResolver($this->app['db']);
			Extension::setEventDispatcher($this->app['events']);
			Extension::setMigrator($this->app['migrator']);

			$this->app['extensions']->findAndRegisterExtensions();
			$this->app['extensions']->sortExtensions();

			// Now we will check if the extensions should be auto-booted.
			if ($this->app['config']['cartalyst/extensions::auto_boot'])
			{
				foreach ($this->app['extensions'] as $extension)
				{
					$extension->setupDatabase();
				}

				foreach ($this->app['extensions']->allEnabled() as $extension)
				{
					$extension->boot();
				}
			}
		}
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerExtensionsFinder();
		$this->registerExtensions();
	}

	/**
	 * Registers the extensions finder.
	 *
	 * @return void
	 */
	protected function registerExtensionsFinder()
	{
		$this->app['extensions.finder'] = $this->app->share(function($app)
		{
			$paths = $app['config']['cartalyst/extensions::paths'];

			return new FileFinder($app['files'], $paths);
		});
	}

	/**
	 * Registers the extensions bag.
	 *
	 * @return void
	 */
	protected function registerExtensions()
	{
		$this->app['extensions'] = $this->app->share(function($app)
		{
			return new ExtensionBag($app['files'], $app['extensions.finder'], $app);
		});
	}

}
