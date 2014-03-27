<?php namespace Cartalyst\Media;
/**
 * Part of the Media package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Media
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Intervention\Image\ImageServiceProvider;

class MediaServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('cartalyst/media', 'cartalyst/media', __DIR__.'/../..');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Register the Intervention Image service provider.
		$this->app->instance('intervention', $provider = new ImageServiceProvider($this->app));
		$this->app->register($provider);
		$this->app['intervention']->boot();
		unset($this->app['intervention']);

		// Register the Intervention Image class alias
		AliasLoader::getInstance()->alias('Image', 'Intervention\Image\Facades\Image');

		$this->app['media'] = $this->app->share(function($app)
		{
			$config = $app['config']->get('cartalyst/media::config');

			$adapter = new Local(array_get($config, 'adapters.local.storage_path'));

			$filesystem = new Filesystem($adapter);

			$media = new Media($filesystem, $app['events']);

			$media->setDispersion($config['dispersion']);

			$media->setMaxFileSize($config['max_filesize']);

			$media->setAllowedMimes($config['allowed_mimes']);

			$media->setPlaceholders($config['placeholders']);

			return $media;
		});
	}

}
