<?php namespace Cartalyst\Themes\Locations;
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

interface GeneratorInterface {

	/**
	 * Returns a path relative to the public directory.
	 *
	 * @param  string  $relativePath
	 * @return strin
	 */
	public function getPublicPath($relativePath);

	/**
	 * Returns the URL for the relative URI.
	 *
	 * @param  string  $uri
	 * @return string
	 */
	public function getUrl($uri);

	/**
	 * Returns the corresponding URL for the given fully qualified path.
	 * If a URL cannot be determined a Runtime Exception is thrown.
	 *
	 * @param  string  $path
	 * @return string
	 * @throws \RuntimeException
	 */
	public function getPathUrl($path);

}
