<?php
/**
 * Part of the Platform application.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Platform
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Mockery as m;
use Illuminate\Container\Container;
use Cartalyst\Widgets\WidgetResolver;

class WidgetResolverTest extends PHPUnit_Framework_TestCase {

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
	 * @expectedException InvalidArgumentException
	 */
	public function testParsingKeyThrowsExceptionIfExtensionIsNotGiven()
	{
		$resolver = new WidgetResolver(
			$container    = new Container,
			$extensionBag = m::mock('Cartalyst\Extensions\ExtensionBag')
		);

		$resolver->parseKey('baz.bat.qux');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParsingKeyThrowsExceptionIfExtensionDoesNotExist()
	{
		$resolver = new WidgetResolver(
			$container    = new Container,
			$extensionBag = m::mock('Cartalyst\Extensions\ExtensionBag[offsetExists]')
		);

		$extensionBag->shouldReceive('offsetExists')->with('foo/bar')->once()->andReturn(false);

		$resolver->parseKey('foo/bar::baz.bat.qux');
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testParsingKeyThrowsExceptionIfExtensionIsNotEnabled()
	{
		$resolver = new WidgetResolver(
			$container    = new Container,
			$extensionBag = m::mock('Cartalyst\Extensions\ExtensionBag[offsetExists,offsetGet]')
		);

		$extensionBag->shouldReceive('offsetExists')->with('foo/bar')->andReturn(true);
		$extensionBag->shouldReceive('offsetGet')->with('foo/bar')->andReturn($extension1 = m::mock('Cartalyst\Extensions\ExtensionInterface'));
		$extension1->shouldReceive('isEnabled')->andReturn(false);

		// Only used for exception
		$extension1->shouldReceive('getSlug')->once()->andReturn('foo/bar');

		$resolver->parseKey('foo/bar::baz.bat.qux');
	}

	public function testParsingKeyReturnsCorrectClass()
	{
		$resolver = new WidgetResolver(
			$container    = new Container,
			$extensionBag = m::mock('Cartalyst\Extensions\ExtensionBag[offsetExists,offsetGet]')
		);

		$extensionBag->shouldReceive('offsetExists')->with('foo/bar')->andReturn(true);
		$extensionBag->shouldReceive('offsetGet')->with('foo/bar')->andReturn($extension1 = m::mock('Cartalyst\Extensions\ExtensionInterface'));
		$extension1->shouldReceive('getNamespace')->once()->andReturn('Foo\Bar');
		$extension1->shouldReceive('isEnabled')->once()->andReturn(true);

		// Double check we did get an array with two indexes
		$this->assertCount(2, $actual = $resolver->parseKey('foo/bar::baz.bat'));

		// Order matters so we'll inspect each individually
		$expected = array('Foo\Bar\Widgets\Baz', 'bat');
		$this->assertEquals($expected[0], $actual[0]);
		$this->assertEquals($expected[1], $actual[1]);
	}

	public function testParsingKeyReturnsCorrectClassWithPrefix()
	{
		$resolver = new WidgetResolver(
			$container    = new Container,
			$extensionBag = m::mock('Cartalyst\Extensions\ExtensionBag[offsetExists,offsetGet]')
		);

		$extensionBag->shouldReceive('offsetExists')->with('foo/bar')->andReturn(true);
		$extensionBag->shouldReceive('offsetGet')->with('foo/bar')->andReturn($extension1 = m::mock('Cartalyst\Extensions\ExtensionInterface'));
		$extension1->shouldReceive('getNamespace')->once()->andReturn('Foo\Bar');
		$extension1->shouldReceive('isEnabled')->once()->andReturn(true);

		// Double check we did get an array with two indexes
		$this->assertCount(2, $actual = $resolver->parseKey('foo/bar::baz.bat', 'Corge'));

		// Order matters so we'll inspect each individually
		$expected = array('Foo\Bar\Corge\Baz', 'bat');
		$this->assertEquals($expected[0], $actual[0]);
		$this->assertEquals($expected[1], $actual[1]);
	}

}
