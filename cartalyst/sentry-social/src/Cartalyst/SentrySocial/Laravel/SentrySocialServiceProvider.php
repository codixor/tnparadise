<?php namespace Cartalyst\SentrySocial\Laravel;
/**
 * Part of the Sentry Social package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Sentry
 * @version    3.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\SentrySocial\Links\IlluminateLinkRepository;
use Cartalyst\SentrySocial\Manager;
use Cartalyst\SentrySocial\RequestProviders\IlluminateRequestProvider;
use Cartalyst\Sentry\Sessions\IlluminateSession;

class SentrySocialServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * {@inheritDoc}
	 */
	protected $defer = true;

	/**
	 * {@inheritDoc}
	 */
	public function boot()
	{
		$this->package('cartalyst/sentry-social', 'cartalyst/sentry-social', __DIR__.'/../../..');
	}

	/**
	 * {@inheritDoc}
	 */
	public function register()
	{
		$this->registerLinkProvider();
		$this->registerRequestProvider();
		$this->registerSession();
		$this->registerSentrySocial();
	}

	protected function registerLinkProvider()
	{
		$this->app['sentry.social.links'] = $this->app->share(function($app)
		{
			$model = $app['config']['cartalyst/sentry-social::link'];

			$users = $app['config']['cartalyst/sentry::users.model'];
			if (class_exists($model) and method_exists($model, 'setUsersModel'))
			{
				forward_static_call_array(array($model, 'setUsersModel'), array($users));
			}

			return new IlluminateLinkRepository($model);
		});
	}

	protected function registerRequestProvider()
	{
		$this->app['sentry.social.request'] = $this->app->share(function($app)
		{
			return new IlluminateRequestProvider($app['request']);
		});
	}

	protected function registerSession()
	{
		$this->app['sentry.social.session'] = $this->app->share(function($app)
		{
			$key = $app['config']['cartalyst/sentry::cookie.key'].'_social';

			return new IlluminateSession($app['session.store'], $key);
		});
	}

	/**
	 * Registers Sentry Social.
	 *
	 * @return void
	 */
	protected function registerSentrySocial()
	{
		$this->app['sentry.social'] = $this->app->share(function($app)
		{
			$manager = new Manager(
				$app['sentry'],
				$app['sentry.social.links'],
				$app['sentry.social.request'],
				$app['sentry.social.session'],
				$app['events']
			);

			$connections = $app['config']['cartalyst/sentry-social::connections'];

			$manager->addConnections($connections);

			return $manager;
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function provides()
	{
		return array(
			'sentry.social.links',
			'sentry.social.request',
			'sentry.social.session',
			'sentry.social',
		);
	}

}
