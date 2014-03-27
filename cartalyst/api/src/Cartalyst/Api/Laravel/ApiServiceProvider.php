<?php namespace Cartalyst\Api\Laravel;
/**
 * Part of the API package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    API
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Support\ServiceProvider;
use Cartalyst\Api\Dispatcher;
use Cartalyst\Api\Router;

class ApiServiceProvider extends ServiceProvider {

	/**
	 * {@inheritDoc}
	 */
	protected $defer = false;

	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		$this->package('cartalyst/api', 'cartalyst/api', __DIR__.'/../../..');

		$this->overrideRouter();
	}

	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		$this->app['api.router'] = $this->app->share(function($app)
		{
			$router = new Router($app['events'], $app);

			if ($app['env'] == 'testing')
			{
				$router->disableFilters();
			}

			return $router;
		});

		$this->app['api'] = $this->app->share(function($app)
		{
			return new Dispatcher($app);
		});
	}

	/**
	 * Override the Laravel router.
	 *
	 * @return void
	 */
	protected function overrideRouter()
	{
		$this->app['router'] = $this->app->share(function($app)
		{
			return $app['api.router'];
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function provides()
	{
		return array('router');
	}

}
