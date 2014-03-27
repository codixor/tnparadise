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
use Cartalyst\Extensions\ExtensionBag;
use Illuminate\Container\Container;
use PHPUnit_Framework_TestCase;

class ExtensionBagTest extends PHPUnit_Framework_TestCase {

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
	 * @expectedException RuntimeException
	 */
	public function testCreatingExtensionFailsForMalformedContents()
	{
		$bag = new ExtensionBag(
			$filesystem = m::mock('Illuminate\Filesystem\Filesystem'),
			$finder = m::mock('Cartalyst\Extensions\FinderInterface')
		);

		$filesystem->shouldReceive('getRequire')->with($file = 'foo/bar/extension.php')->once()->andReturn('foo');

		$bag->create($file);
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testCreatingExtensionFailsForMissingSlug()
	{
		$bag = new ExtensionBag(
			$filesystem = m::mock('Illuminate\Filesystem\Filesystem'),
			$finder = m::mock('Cartalyst\Extensions\FinderInterface')
		);

		$filesystem->shouldReceive('getRequire')->with($file = 'foo/bar/extension.php')->once()->andReturn(array('not_slug' => 'foo/bar'));

		$bag->create($file);
	}

	public function testRegisteringExtensions()
	{
		$bag = m::mock('Cartalyst\Extensions\ExtensionBag[create]');

		$bag->shouldReceive('create')->with('foo/bar')->once()->andReturn($extension = m::mock('Cartalyst\Extensions\ExtensionInterface'));
		$extension->shouldReceive('getSlug')->once()->andReturn('foo/bar');
		$extension->shouldReceive('register')->once();

		$bag->register('foo/bar');
	}

	public function testExtensionsCanBeRegisteredOnConstruct()
	{
		$bag = m::mock('Cartalyst\Extensions\ExtensionBag[register]');

		$bag->shouldReceive('register')->with('foo/bar')->once();

		$bag->__construct(
			$filesystem = m::mock('Illuminate\Filesystem\Filesystem'),
			$finder = m::mock('Cartalyst\Extensions\FinderInterface'),
			$container = new Container,
			array('foo/bar')
		);
	}

	public function testExtensionsCanBeSorted()
	{
		$bag = new ExtensionBag(
			$filesystem = m::mock('Illuminate\Filesystem\Filesystem'),
			$finder = m::mock('Cartalyst\Extensions\FinderInterface')
		);

		$extension1 = m::mock('Cartalyst\Extensions\ExtensionInterface');
		$extension1->shouldReceive('getSlug')->times(3)->andReturn('foo/bar');
		$extension1->shouldReceive('getDependencies')->once()->andReturn(array(
			'baz/qux',
		));
		$extension1->shouldReceive('register')->once();

		$bag->register($extension1);

		$extension2 = m::mock('Cartalyst\Extensions\ExtensionInterface');
		$extension2->shouldReceive('getSlug')->times(3)->andReturn('baz/qux');
		$extension2->shouldReceive('getDependencies')->once()->andReturn(array());
		$extension2->shouldReceive('register')->once();

		$bag->register($extension2);

		$bag->sortExtensions();

		$expected = array('baz/qux', 'foo/bar');
		$this->assertEquals(implode('.', $expected), implode('.', array_map(function($extension)
		{
			return $extension->getSlug();
		}, $bag->all())));
	}

}
