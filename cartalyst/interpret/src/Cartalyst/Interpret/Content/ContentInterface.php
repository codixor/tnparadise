<?php namespace Cartalyst\Interpret\Content;
/**
 * Part of the Interpret package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Interpret
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

interface ContentInterface {

	/**
	 * Creates a new content instance.
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function __construct($value);

	/**
	 * Returns the content's value.
	 *
	 * @return string
	 */
	public function getValue();

	/**
	 * Returns the HTML equivilent of the content.
	 *
	 * @return string
	 */
	public function toHtml();

}
