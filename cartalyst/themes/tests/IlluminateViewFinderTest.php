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
use Cartalyst\Themes\Views\IlluminateViewFinder;
use Illuminate\Filesystem\Filesystem;
use PHPUnit_Framework_TestCase;

class IlluminateViewFinderTest extends PHPUnit_Framework_TestCase {

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

	public function testNativeLoadingIsTriggeredIfTheThemeBagIsNotSet()
	{
		$finder = new IlluminateViewFinder($filesystem = m::mock('Illuminate\Filesystem\Filesystem'), array(__DIR__));
		$filesystem->shouldReceive('exists')->with($file = __DIR__.'/foo.blade.php')->once()->andReturn(true);
		$this->assertEquals($file, $finder->find('foo'));
	}

	public function testNamespacesOverridePackages()
	{
		$finder = new IlluminateViewFinder($filesystem = m::mock('Illuminate\Filesystem\Filesystem'), array(__DIR__));
		$finder->setThemeBag($bag = $this->getMockThemeBag());
		$finder->addNamespace('foo', __DIR__.'/namespaces/foo');

		$bag->shouldReceive('getCascadedNamespaceViewPaths')->with('foo')->once()->andReturn($paths = array('foo/1', 'foo/2'));
		$filesystem->shouldReceive('exists')->with('foo/1/bar.blade.php')->once()->andReturn(false);
		$filesystem->shouldReceive('exists')->with('foo/1/bar.php')->once()->andReturn(false);
		$filesystem->shouldReceive('exists')->with($path = 'foo/2/bar.blade.php')->once()->andReturn(true);

		// Because we use mocks, we know the package method calls are ever made
		$this->assertEquals($path, $finder->find('foo::bar'));
	}

	public function testPackageViews()
	{
		$finder = new IlluminateViewFinder($filesystem = m::mock('Illuminate\Filesystem\Filesystem'), array(__DIR__));
		$finder->setThemeBag($bag = $this->getMockThemeBag());

		$bag->shouldReceive('getCascadedPackageViewPaths')->with('foo')->once()->andReturn($paths = array('foo/1', 'foo/2'));
		$filesystem->shouldReceive('exists')->with('foo/1/bar.blade.php')->once()->andReturn(false);
		$filesystem->shouldReceive('exists')->with('foo/1/bar.php')->once()->andReturn(false);
		$filesystem->shouldReceive('exists')->with($path = 'foo/2/bar.blade.php')->once()->andReturn(true);

		$this->assertEquals($path, $finder->find('foo::bar'));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testNonExistentViewThrowsException()
	{
		$finder = new IlluminateViewFinder($filesystem = m::mock('Illuminate\Filesystem\Filesystem'), array(__DIR__));
		$finder->setThemeBag($bag = $this->getMockThemeBag());

		$bag->shouldReceive('getCascadedPackageViewPaths')->with('foo')->once()->andReturn($paths = array('foo/1'));
		$filesystem->shouldReceive('exists')->with('foo/1/bar.blade.php')->once()->andReturn(false);
		$filesystem->shouldReceive('exists')->with('foo/1/bar.php')->once()->andReturn(false);

		$bag->shouldReceive('getActive')->once()->andReturn($active = m::mock('Cartalyst\Themes\ThemeInterface'));
		$active->shouldReceive('getSlug')->once()->andReturn('foo/bar');
		$active->shouldReceive('getParentSlug')->once()->andReturn('foo/baz');
		$bag->shouldReceive('getFallback')->once()->andReturn($fallback = m::mock('Cartalyst\Themes\ThemeInterface'));
		$fallback->shouldReceive('getSlug')->once()->andReturn('foo/corge');

		$finder->find('foo::bar');
	}

	protected function getMockThemeBag()
	{
		$themeBag = m::mock('Cartalyst\Themes\ThemeBag');
		$themeBag->shouldReceive('getFilesystem')->andReturn(m::mock('Illuminate\Filesystem\Filesystem'));
		return $themeBag;
	}

}
