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
use Cartalyst\Themes\Theme;
use PHPUnit_Framework_TestCase;

class ThemeTest extends PHPUnit_Framework_TestCase {

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public function setUp()
	{

	}

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
	public function testReadingInvalidJson()
	{
		$bag = $this->getMockThemeBag();
		$bag->getFilesystem()->shouldReceive('get')->with(__DIR__.'/theme.json')->once()->andReturn(<<<JSON
{
	"slug": "foo",
}
JSON
		);

		$theme = new Theme($bag, __DIR__);
	}

	public function testReadingJsonSetsUpTheme()
	{
		$bag = $this->getMockThemeBag();
		$bag->getFilesystem()->shouldReceive('get')->with(__DIR__.'/theme.json')->once()->andReturn(<<<JSON
{
	"slug": "foo::bar/baz",
	"author": "Cartalyst LLC"
}
JSON
		);

		$theme = new Theme($bag, __DIR__);
		$this->assertEquals('foo', $theme->getArea());
		$this->assertEquals('bar/baz', $theme->getKey());
		$this->assertEquals('foo::bar/baz', $theme->getSlug());
		$this->assertEquals(array('author' => 'Cartalyst LLC'), $theme->getAttributes());
	}

	protected function getMockThemeBag()
	{
		$themeBag = m::mock('Cartalyst\Themes\ThemeBag');
		$themeBag->shouldReceive('getFilesystem')->andReturn(m::mock('Illuminate\Filesystem\Filesystem'));
		return $themeBag;
	}

}
