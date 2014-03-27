<?php namespace Cartalyst\Themes;
/**
 * Part of the Themes package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Themes
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Themes\Assets\AssetManager;
use Cartalyst\Themes\Console\ThemePublishCommand;
use Cartalyst\Themes\Locations\IlluminateGenerator as IlluminateLocationGenerator;
use Cartalyst\Themes\Views\IlluminateViewFinder;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewServiceProvider;

class ThemeServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('cartalyst/themes', 'cartalyst/themes');

		$this->overrideViewFinder();

		if ($active = $this->app['config']->get('cartalyst/themes::active'))
		{
			$this->app['themes']->setActive($active);
		}

		if ($fallback = $this->app['config']->get('cartalyst/themes::fallback'))
		{
			$this->app['themes']->setFallback($fallback);
		}

		$this->app['themes']->setViewEnvironment($this->app['view']);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerThemeBag();
		$this->registerLocationGenerator();
		$this->registerAssetManager();
		$this->registerThemePublisher();

		$this->commands('command.theme.publish');
	}

	/**
	 * Attempts to guess whether the application is in debug mode
	 * or not, which will affect the assets compilation.
	 *
	 * @return bool
	 */
	public function guessDebug()
	{
		$debug = $this->app['config']->get('cartalyst/themes::debug', null);

		if (is_null($debug) or ! $debug or $this->app['env'] === 'production') return false;

		return true;
	}

	/**
	 * Register the theme bag which holds all the themes.
	 *
	 * @return void
	 */
	protected function registerThemeBag()
	{
		$this->app['themes'] = $this->app->share(function($app)
		{
			$themeBag = new ThemeBag($app['files'], $app['config']['cartalyst/themes::paths']);

			$themeBag->setPackagesPath($app['config']['cartalyst/themes::packages_path']);
			$themeBag->setNamespacesPath($app['config']['cartalyst/themes::namespaces_path']);
			$themeBag->setViewsPath($app['config']['cartalyst/themes::views_path']);
			$themeBag->setAssetsPath($app['config']['cartalyst/themes::assets_path']);

			return $themeBag;
		});
	}

	/**
	 * Register the location generator which returns a bunch of URLs and
	 * paths to assets.
	 *
	 * @return void
	 */
	protected function registerLocationGenerator()
	{
		$this->app['theme.locations'] = $this->app->share(function($app)
		{
			return new IlluminateLocationGenerator($app['url'], $app['path.public']);
		});
	}

	/**
	 * Register the asset manager itself, which is responsible for holding all
	 * assets and compiling them.
	 *
	 * @return void
	 */
	protected function registerAssetManager()
	{
		// The assets and views parts actually take the theme bag as
		// their dependencies. They do this so that all themes in
		// the bag may be used when finding views and assets.
		$me = $this;
		$this->app['theme.assets'] = $this->app->share(function($app) use ($me)
		{
			$publicPath = $app['path.public'];

			$manager = new AssetManager(
				$app['themes'],
				$app['view.finder'],
				$app['theme.locations']
			);

			$manager->setDebug($me->guessDebug());

			$manager->setCachePath($app['config']->get('cartalyst/themes::cache_path', null));

			foreach ($app['config']->get('cartalyst/themes::filters', array()) as $extension => $filters)
			{
				foreach ($filters as $filter) $manager->addFilter($extension, $filter);
			}

			$manager->setForceRecompile($app['config']->get('cartalyst/themes::force_recompile', false));
			$manager->clearAssets($app['config']->get('cartalyst/themes::auto_clear', false));

			return $manager;
		});
	}

	/**
	 * Override the view finder used by Laravel to be our Theme view finder.
	 *
	 * @return void
	 */
	protected function overrideViewFinder()
	{
		$originalViewFinder = $this->app['view.finder'];

		$this->app['view.finder'] = $this->app->share(function($app) use ($originalViewFinder)
		{
			$paths = array_merge(
				$app['config']['view.paths'],
				$originalViewFinder->getPaths()
			);

			$viewFinder = new IlluminateViewFinder($app['files'], $paths, $originalViewFinder->getExtensions());

			$viewFinder->setThemeBag($app['themes']);

			foreach ($originalViewFinder->getPaths() as $location)
			{
				$viewFinder->addLocation($location);
			}

			foreach ($originalViewFinder->getHints() as $namespace => $hints)
			{
				$viewFinder->addNamespace($namespace, $hints);
			}

			return $viewFinder;
		});

		// Now that we have overridden the "view.finder" IoC offest, we
		// need to re-register the environment as we cannot reset it
		// on the Environment at runtime, yet.
		$viewServiceProvider = new ViewServiceProvider($this->app);
		$viewServiceProvider->registerEnvironment();
	}

	/**
	 * Registers the theme publisher that will be used.
	 *
	 * @return void
	 */
	protected function registerThemePublisher()
	{
		$this->app['theme.publisher'] = $this->app->share(function($app)
		{
			$publisher = new ThemePublisher($app['themes']);

			$publisher->setPackagePath($app['path.base'].'/vendor');

			$publisher->setDispatcher($app['events']);

			return $publisher;
		});

		$this->app['command.theme.publish'] = $this->app->share(function($app)
		{
			return new ThemePublishCommand($app['theme.publisher']);
		});
	}

}
