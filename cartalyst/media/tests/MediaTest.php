<?php namespace Cartalyst\Media\Tests;
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

use Cartalyst\Media\Media;
use Mockery as m;
use PHPUnit_Framework_TestCase;

use Flysystem\Filesystem;
use Illuminate\Events\Dispatcher;

class MediaTest extends PHPUnit_Framework_TestCase {

	/**
	 * Holds the Media instance.
	 *
	 * @var \Cartalyst\Media\Media
	 */
	protected $media;

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	/**
	 * Setup resources and dependencies
	 */
	public function _setUp()
	{
		$adapter = new Local(__DIR__ . '/media');

		$filesystem = new Filesystem($adapter);

		$this->media = new Media($filesystem, new Dispatcher);

		$this->media->setMaxFileSize(10); // 10 MB

		$this->media->setAllowedMimes(array(

			'image/jpeg',

		));
	}

	public function testMediaCanBeInstantiated()
	{

	}

}
