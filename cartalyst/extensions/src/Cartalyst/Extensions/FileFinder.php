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

use Illuminate\Filesystem\Filesystem;

class FileFinder implements FinderInterface {

	/**
	 * Filesystem instance.
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * The array of paths.
	 *
	 * @var array
	 */
	protected $paths = array();

	/**
	 * Create a new file finder instance
	 *
	 * @param  Illuminate\Filesystem\Filesystem  $filesystem
	 * @param  array  $paths
	 * @param  string  $class
	 * @return void
	 */
	public function __construct(Filesystem $filesystem, array $paths)
	{
		$this->filesystem = $filesystem;
		$this->paths      = $paths;
	}

	/**
	 * Returns an array of fully qualified extension locations in the
	 * registered paths.
	 *
	 * @return array
	 */
	public function findExtensions()
	{
		$extensions = array();

		foreach ($this->paths as $path)
		{
			$extensions = array_merge($extensions, $this->findExtensionsInPath($path));
		}

		return $extensions;
	}

	/**
	 * Adds a path to the extensions finder.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function addPath($path)
	{
		$this->paths[] = $path;
	}

	/**
	 * Returns an array of fully qualified extension locations in
	 * the given path.
	 *
	 * @param  string  $path
	 * @return array
	 */
	public function findExtensionsInPath($path)
	{
		$extensions = $this->filesystem->glob($path.'/*/*/extension.php');

		if ($extensions === false)
		{
			return array();
		}

		return $extensions;
	}

}
