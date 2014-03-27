<?php namespace Cartalyst\Themes\Console;
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

use Cartalyst\Extensions\ExtensionInterface;
use Cartalyst\Themes\ThemePublisher;
use Closure;
use Illuminate\Console\Command;
use InvalidArgumentException;
use JasonLewis\ResourceWatcher\Watcher;
use JasonLewis\ResourceWatcher\Tracker;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ThemePublishCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:publish';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish themes and their assets';

	/**
	 * The theme publisher used by the command.
	 *
	 * @var \Cartalyst\Themes\ThemePublisher
	 */
	protected $publisher;

	/**
	 * The resource watcher.
	 *
	 * @var \JasonLewis\ResourceWatcher\Watcher
	 */
	protected $watcher;

	/**
	 * Create a new theme publish command instance.
	 *
	 * @param  \Cartalyst\Themes\ThemePublisher  $publisher
	 * @return void
	 */
	public function __construct(ThemePublisher $publisher)
	{
		parent::__construct();

		$this->publisher = $publisher;

		$me = $this;
		$this->publisher->noting(function($note) use ($me)
		{
			$me->line($note);
		});
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->watcher = new Watcher(new Tracker, $this->laravel['files']);

		if ($package = $this->input->getOption('package'))
		{
			$this->publishPackage($package);
		}
		elseif ($slug = $this->input->getOption('extension'))
		{
			$this->publishExtension($this->laravel['extensions'][$slug]);
		}
		elseif ($this->input->getOption('extensions'))
		{
			$this->publishExtensions();
		}
		else
		{
			throw new \RuntimeException('Failed to provide valid publish option.');
		}

		// If we are watching, we'll create a resource listener
		// for the given option and continue to publish it after
		// the initial publish (which will happen below).
		if ($this->input->getOption('watch'))
		{
			if ($package = $this->input->getOption('package'))
			{
				$this->createPackageListener($package);
			}

			elseif ($slug = $this->input->getOption('extension'))
			{
				$this->createExtensionListener($this->laravel['extensions'][$slug]);
			}

			elseif ($this->input->getOption('extensions'))
			{
				$this->createExtensionsListener();
			}

			$this->startWatching();
		}
	}

	/**
	 * Publish the given package resources.
	 *
	 * @param  string  $package
	 * @return bool
	 */
	public function publishPackage($package)
	{
		return $this->publisher->publishPackage($package);
	}

	/**
	 * Publish the given extension resources.
	 *
	 * @param  \Cartalyst\Extensions\ExtensionInterface  $extension
	 * @return bool
	 */
	public function publishExtension(ExtensionInterface $extension)
	{
		return $this->publisher->publishExtension($extension);
	}

	/**
	 * Publish all the extensions resources.
	 *
	 * @return bool
	 */
	public function publishExtensions()
	{
		foreach ($this->laravel['extensions']->all() as $extension)
		{
			try
			{
				$this->publishExtension($extension);
			}

			// Invalid argument exceptions are given if the source directory
			// doesn't exist. This may be perfectly true in an extension.
			catch (InvalidArgumentException $e)
			{
				$this->line($e->getMessage());
			}

			catch (RuntimeException $e)
			{
				$this->error($e->getMessage());
			}
		}

		return true;
	}

	protected function createPackageListener($package)
	{
		$source = $this->publisher->getPackagePublishSource($package);

		$me = $this;
		$this->createListener($source, function($resource) use ($me, $package)
		{
			$me->publishPackage($package);
		});
	}

	protected function createExtensionListener(ExtensionInterface $extension)
	{
		$source = $this->publisher->getExtensionPublishSource($extension);

		$me = $this;
		$this->createListener($source, function($resource) use ($me, $extension)
		{
			$me->publishExtension($extension);
		});
	}

	protected function createExtensionsListener()
	{
		foreach ($this->laravel['extensions']->all() as $extension)
		{
			$this->createExtensionListener($extension);
		}
	}

	protected function createListener($source, Closure $callback)
	{
		$listener = $this->watcher->watch($source);

		$listener->onCreate($callback);
		$listener->onModify($callback);
		$listener->onDelete($callback);

		return $listener;
	}

	protected function startWatching()
	{
		$this->watcher->startWatch();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('package', null, InputOption::VALUE_OPTIONAL, 'The name of the package to publish.', null),

			array('extension', null, InputOption::VALUE_OPTIONAL, 'The name of the extension to publish.', null),

			array('extensions', null, InputOption::VALUE_NONE, 'Publish all extensions.'),

			array('watch', null, InputOption::VALUE_NONE, 'Watch for changes and autopublish.'),
		);
	}

}
