<?php namespace Cartalyst\CompositeConfig;
/**
 * Part of the Composite Config package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Composite Config
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Support\ServiceProvider;

class CompositeConfigServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('cartalyst/composite-config', 'cartalyst/composite-config');

		$originalLoader = $this->app['config']->getLoader();

		// We will grab the new loader and syncronize all of the namespaces.
		$compositeLoader = $this->app['config.loader.composite'];
		foreach ($originalLoader->getNamespaces() as $namespace => $hint)
		{
			$compositeLoader->addNamespace($namespace, $hint);
		}

		$table = $this->app['config']['cartalyst/composite-config::table'];

		// Now we will set the config loader instance.
		unset($this->app['config.loader.composite']);
		$this->app['config']->setLoader($compositeLoader);

		// Set the database property on the composite loader so it will now
		// merge database configuration with file configuration.
		if ($this->databaseIsReady($table))
		{
			$compositeLoader->setDatabase($this->app['db']->connection());
			$compositeLoader->setDatabaseTable($table);
		}

		// We'll also set the repository
		$compositeLoader->setRepository($this->app['config']);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$compositeLoader = new CompositeLoader($this->app['files'], $this->app['path'].'/config');

		$this->app->instance('config.loader.composite', $compositeLoader);
	}

	protected function databaseIsReady($table)
	{
		try
		{
			$this->app['db']->connection()->table($table)->get();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

}
