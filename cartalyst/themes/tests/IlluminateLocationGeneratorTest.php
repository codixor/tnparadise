<?php namespace Cartalyst\Themes\Tests;
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

use Mockery as m;
use Cartalyst\Themes\Locations\IlluminateGenerator;
use PHPUnit_Framework_TestCase;

class IlluminateLocationGeneratorTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testGettingUrl()
	{
		$locationGenerator = new IlluminateGenerator($urlGenerator = m::mock('Illuminate\Routing\UrlGenerator'), __DIR__);
		$urlGenerator->shouldReceive('to')->with($uri = 'foo?bar=baz')->once()->andReturn($url = 'http://www.example.com');
		$this->assertEquals($url, $locationGenerator->getUrl($uri));
	}

	public function testPathUrl()
	{
		$locationGenerator = new IlluminateGenerator($urlGenerator = m::mock('Illuminate\Routing\UrlGenerator'), __DIR__);
		$urlGenerator->shouldReceive('asset')->with('foo/baz')->once()->andReturn($url = 'http://www.example.com');
		$this->assertEquals($url, $locationGenerator->getPathUrl(__DIR__.'/foo/bar/../baz'));
	}

}
