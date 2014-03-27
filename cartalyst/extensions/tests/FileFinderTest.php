<?php namespace Cartalyst\Extensions\Tests;
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

use Mockery as m;
use Cartalyst\Extensions\FileFinder;
use PHPUnit_Framework_TestCase;

class FileFinderTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testFindingExtensionsInPath()
	{
		$finder = new FileFinder(
			$filesystem = m::mock('Illuminate\Filesystem\Filesystem'),
			$paths = array('foo')
		);

		$filesystem->shouldReceive('glob')->with('foo/*/*/extension.php')->once()->andReturn(false);
		$this->assertEquals(array(), $finder->findExtensionsInPath('foo'));

		$filesystem->shouldReceive('glob')->with('bar/*/*/extension.php')->once()->andReturn($expected = array(
			'bar/baz/qux/extension.php',
		));
		$this->assertEquals($expected, $finder->findExtensionsInPath('bar'));
	}

	public function testFindingExtensions()
	{
		$finder = m::mock('Cartalyst\Extensions\FileFinder[findExtensionsInPath]');
		$finder->__construct(
			$filesystem = m::mock('Illuminate\Filesystem\Filesystem'),
			$paths = array('foo')
		);
		$finder->shouldReceive('findExtensionsInPath')->with('foo')->twice()->andReturn(array('bar'));

		$this->assertEquals(array('bar'), $finder->findExtensions());

		$finder->addPath('baz');
		$finder->shouldReceive('findExtensionsInPath')->with('baz')->once()->andReturn(array('qux'));

		$this->assertEquals(array('bar', 'qux'), $finder->findExtensions());
	}

}
