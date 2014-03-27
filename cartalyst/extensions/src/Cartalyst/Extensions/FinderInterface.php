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

interface FinderInterface {

	/**
	 * Finds all extensions and returns them.
	 *
	 * @return array
	 */
	public function findExtensions();

	/**
	 * Adds a path to the extensions finder.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function addPath($path);

}
