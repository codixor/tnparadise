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

use ArrayAccess;
use Cartalyst\Dependencies\DependencySorter;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class ExtensionBag extends Collection {

	/**
	 * Filesystem instance.
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * Finder instance which finds extnesions for the
	 * bag.
	 *
	 * @var Cartalyst\Extensions\FinderInterface
	 */
	protected $finder;

	/**
	 * The IoC container instance.
	 *
	 * @var Illuminate\Container\Container
	 */
	protected $container;

	/**
	 * Create a new extension instance.
	 *
	 * @param  Illuminate\Filesystem\Filesystem  $filesystem
	 * @param  array  $extensions
	 * @return void
	 */
	public function __construct(Filesystem $filesystem, FinderInterface $finder, Container $container = null, array $extensions = null)
	{
		$this->container  = $container;
		$this->filesystem = $filesystem;
		$this->finder     = $finder;

		if (isset($container))
		{
			$this->container = $container;
		}

		if (isset($extensions))
		{
			foreach ($extensions as $extension)
			{
				$this->register($extension);
			}
		}
	}

	/**
	 * Creates an Extension from the given fully qualified extension file.
	 *
	 * @param  string  $file
	 * @return Cartalyst\Extensions\ExtensionInterface
	 */
	public function create($file)
	{
		$attributes = $this->filesystem->getRequire($file);

		if ( ! is_array($attributes) or ! isset($attributes['slug']))
		{
			throw new \RuntimeException("Malformed extension.php at path [$file].");
		}

		$slug = $attributes['slug'];
		unset($attributes['slug']);

		$namespace = null;
		if (isset($attributes['namespace']))
		{
			$namespace = $attributes['namespace'];
			unset($attributes['namespace']);
		}

		return new Extension($this, $slug, dirname($file), $attributes, $namespace);
	}

	/**
	 * Registers an extension with the bag.
	 *
	 * @param  mixed  $extension
	 * @return void
	 */
	public function register($extension)
	{
		if (is_string($extension))
		{
			$extension = $this->create($extension);
		}

		$this->registerInstance($extension);
	}

	/**
	 * Sorts all registered extensions by their dependencies.
	 *
	 * @return void
	 */
	public function sortExtensions()
	{
		$sorter = new DependencySorter;

		foreach ($this->all() as $extension)
		{
			$sorter->add($extension->getSlug(), $extension->getDependencies());
		}

		$extensions = array();
		foreach ($sorter->sort() as $slug)
		{
			$extensions[$slug] = $this->items[$slug];
		}
		$this->items = $extensions;
		unset($extensions);

		return;

		// WTF, to fix.
		foreach (with(new DependencySorter($this->items))->sort() as $extension)
		{
			$this->items[$extension->getSlug()] = $extension;
		}

		die();
	}

	/**
	 * Finds and registers Extensions with the Extension Bag.
	 *
	 * @return void
	 */
	public function findAndRegisterExtensions()
	{
		foreach ($this->finder->findExtensions() as $extension)
		{
			$this->register($extension);
		}
	}

	/**
	 * Returns all uninstalled extensions.
	 *
	 * @return array
	 */
	public function allUninstalled()
	{
		return array_filter($this->all(), function(ExtensionInterface $extension)
		{
			return $extension->isUninstalled();
		});
	}

	/**
	 * Returns all installed extensions.
	 *
	 * @return array
	 */
	public function allInstalled()
	{
		return array_filter($this->all(), function(ExtensionInterface $extension)
		{
			return $extension->isInstalled();
		});
	}

	/**
	 * Returns all installed but disabled extensions.
	 *
	 * @return array
	 */
	public function allDisabled()
	{
		return array_filter($this->all(), function(ExtensionInterface $extension)
		{
			return $extension->isDisabled();
		});
	}

	/**
	 * Returns all installed and enabled extensions.
	 *
	 * @return array
	 */
	public function allEnabled()
	{
		return array_filter($this->all(), function(ExtensionInterface $extension)
		{
			return $extension->isEnabled();
		});
	}

	/**
	 * Sets the IoC container associated with
	 * extensions.
	 *
	 * @param  Illuminate\Container\Container  $container
	 * @return void
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Gets the IoC container associated with
	 * extensions.
	 *
	 * @return Illuminate\Container\Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Registers an instance of an extension with the bag.
	 *
	 * @param  Cartalyst\Extensions\ExtensionInterface  $extension
	 * @return void
	 */
	protected function registerInstance(ExtensionInterface $extension)
	{
		$extension->register();

		$this->items[$extension->getSlug()] = $extension;
	}

}
