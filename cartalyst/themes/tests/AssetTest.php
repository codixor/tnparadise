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
use Cartalyst\Themes\Assets\Asset;
use PHPUnit_Framework_TestCase;

class AssetTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testAssetSlug()
	{
		$asset = new Asset(__FILE__);
		$this->assertNull($asset->getSlug());
		$asset->setSlug('foo');
		$this->assertEquals('foo', $asset->getSlug());
	}

	public function testAssetKey()
	{
		$asset = new Asset(__FILE__);
		$this->assertNull($asset->getKey());
		$asset->setKey('foo');
		$this->assertEquals('foo', $asset->getKey());
	}

	public function testDependencies()
	{
		$asset = new Asset(__FILE__);
		$this->assertEquals(array(), $asset->getDependencies());
		$asset->setDependencies(array('foo', 'bar'));
		$this->assertEquals(array('foo', 'bar'), $asset->getDependencies());
	}

}
