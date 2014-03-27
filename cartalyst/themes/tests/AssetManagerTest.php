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
use Assetic\Asset\AssetCollection;
use Cartalyst\Themes\Assets\Asset;
use Cartalyst\Themes\Assets\AssetManager;
use FilterStub;
use FilterStubEmpty;
use PHPUnit_Framework_TestCase;

class AssetManagerTest extends PHPUnit_Framework_TestCase {

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass()
	{
		require_once __DIR__.'/stubs/assets/FilterStubs.php';
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

	public function testGettingPathForKey()
	{
		$manager = new AssetManager(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$path = 'css/style.css';
		$themeBag->shouldReceive('getCascadedAssetPaths')->once()->andReturn(array('assets'));
		$themeBag->getFilesystem()->shouldReceive('exists')->with('assets/'.$path)->once()->andReturn(true);
		$manager->getPath($path);
	}

	public function testGettingUrlForKey()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[getPath]');
		$manager->__construct(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$manager->shouldReceive('getPath')->with($path = 'img/file.png')->once()->andReturn('file.png');
		$locationGenerator->shouldReceive('getPathUrl')->with('file.png')->once()->andReturn($url = 'http://www.example.com/file.png');
		$this->assertEquals($url, $manager->getUrl($path));
	}

	public function testCreatingAsset()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[getPath]');
		$manager->__construct(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$manager->shouldReceive('getPath')->with($path = 'css/file.css')->once()->andReturn('foo.css');

		$this->assertInstanceOf('Cartalyst\Themes\Assets\Asset', $asset = $manager->createAsset('file', 'css/file.css'));
		$this->assertEquals('foo.css', $asset->getSourcePath());
		$this->assertEquals('css/file.css', $asset->getKey());
		$this->assertEquals('file', $asset->getSlug());
	}

	public function testCreatingAssetCollection()
	{
		$manager = new AssetManager(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$asset1 = new Asset(__FILE__);
		$asset2 = new Asset(__FILE__);

		$this->assertInstanceOf('Assetic\Asset\AssetCollection', $collection = $manager->createAssetCollection(array($asset1, $asset2)));
		$this->assertCount(2, $assets = $collection->all());
		$this->assertEquals($asset1, $assets[0]);
		$this->assertEquals($asset1, $assets[1]);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testQueuingInvalidAsset()
	{
		$manager = new AssetManager(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$manager->queue('foo', 'foo.foo');
	}

	public function testQueuingStyleAssets()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[createAsset]');
		$manager->__construct(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$manager->shouldReceive('createAsset')->with('foo', 'foo.css', array())->once()->andReturn($asset1 = m::mock('Cartalyst\Themes\Assets\Asset'));
		$manager->shouldReceive('createAsset')->with('bar', 'bar.less', array())->once()->andReturn($asset2 = m::mock('Cartalyst\Themes\Assets\Asset'));
		$manager->shouldReceive('createAsset')->with('bar', 'baz.sass', array())->once()->andReturn($asset3 = m::mock('Cartalyst\Themes\Assets\Asset'));
		$manager->shouldReceive('createAsset')->with('bar', 'qux.scss', array())->once()->andReturn($asset4 = m::mock('Cartalyst\Themes\Assets\Asset'));

		$manager->queue('foo', 'foo.css');
		$manager->queue('bar', 'bar.less');
		$manager->queue('bar', 'baz.sass');
		$manager->queue('bar', 'qux.scss');

		$this->assertCount(4, $styles = $manager->getStyles());
		$this->assertEquals(array($asset1, $asset2, $asset3, $asset4), $styles);
	}

	public function testQueuingScriptAssets()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[createAsset]');
		$manager->__construct(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$manager->shouldReceive('createAsset')->with('foo', 'foo.js', array())->once()->andReturn($asset1 = m::mock('Cartalyst\Themes\Assets\Asset'));
		$manager->shouldReceive('createAsset')->with('bar', 'bar.coffee', array())->once()->andReturn($asset2 = m::mock('Cartalyst\Themes\Assets\Asset'));

		$manager->queue('foo', 'foo.js');
		$manager->queue('bar', 'bar.coffee');

		$this->assertCount(2, $scripts = $manager->getScripts());
		$this->assertEquals(array($asset1, $asset2), $scripts);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSortingInvalidType()
	{
		$manager = new AssetManager(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$manager->getSorted('foo');
	}

	public function testSortingAssets()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[createAsset]');
		$manager->__construct(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$manager->shouldReceive('createAsset')->with('foo', 'foo.js', array('bar'))->once()->andReturn($asset1 = m::mock('Cartalyst\Themes\Assets\Asset'));
		$asset1->shouldReceive('getSlug')->andReturn('foo');
		$asset1->shouldReceive('getDependencies')->andReturn(array('bar'));

		$manager->shouldReceive('createAsset')->with('bar', 'bar.coffee', array())->once()->andReturn($asset2 = m::mock('Cartalyst\Themes\Assets\Asset'));
		$asset2->shouldReceive('getSlug')->andReturn('bar');
		$asset2->shouldReceive('getDependencies')->andReturn(array());

		// An extensive test is not required as most of the heavy
		// lifting is done by a separate package. Let's just test
		// we are sorting.
		$manager->queue('foo', 'foo.js', array('bar'));
		$manager->queue('bar', 'bar.coffee');

		$this->assertCount(2, $scripts = $manager->getSortedScripts());
		$this->assertEquals($asset2, $scripts[0]);
		$this->assertEquals($asset1, $scripts[1]);
	}

	public function testSortingAssetsWhenDependenciesAreInstancesOfAsset()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[createAsset]');
		$manager->__construct(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		// Declare first so we can set it as a dependency for asset 1
		$asset2 = m::mock('Cartalyst\Themes\Assets\Asset');
		$asset2->shouldReceive('getSlug')->andReturn('bar');
		$asset2->shouldReceive('getDependencies')->andReturn(array());

		$manager->shouldReceive('createAsset')->with('foo', 'foo.js', array('bar'))->once()->andReturn($asset1 = m::mock('Cartalyst\Themes\Assets\Asset'));
		$asset1->shouldReceive('getSlug')->andReturn('foo');
		$asset1->shouldReceive('getDependencies')->andReturn(array($asset2));

		$manager->shouldReceive('createAsset')->with('bar', 'bar.coffee', array())->once()->andReturn($asset2);

		$manager->queue('foo', 'foo.js', array('bar'));
		$manager->queue('bar', 'bar.coffee');

		$this->assertCount(2, $scripts = $manager->getSortedScripts());
		$this->assertEquals($asset2, $scripts[0]);
		$this->assertEquals($asset1, $scripts[1]);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGettingCompileExtensionThrowsExceptionForInvalidType()
	{
		$manager = new AssetManager(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$manager->getCompileFileExtension('foo');
	}

	public function testGettingCompileFileExtension()
	{
		$manager = new AssetManager(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$this->assertEquals('css', $manager->getCompileFileExtension('styles'));
		$this->assertEquals('js', $manager->getCompileFileExtension('scripts'));
	}

	public function testFilters()
	{
		$manager = new AssetManager(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$this->assertCount(0, $manager->getFilterInstances('foo'));
		$manager->addFilter('foo', function() { return new FilterStubEmpty; });
		$manager->addFilter('foo', function() { return new FilterStub; });
		$this->assertCount(2, $filters = $manager->getFilterInstances('foo'));
		$this->assertInstanceOf('FilterStubEmpty', reset($filters));
		$this->assertInstanceOf('FilterStub', end($filters));
	}

	public function testCompiling()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[getSorted]');
		$manager->__construct(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);

		$manager->shouldReceive('getSorted')->with('scripts')->once()->andReturn(array(
			$asset1 = new Asset(__FILE__),
			$asset2 = new Asset(__FILE__),
		));

		$manager->addFilter('php', function() { return new FilterStub; });

		$locationGenerator->shouldReceive('getPublicPath')->with('/assets\/cache\/[a-z0-9]+\.js/')->twice()->andReturn('foo');
		$themeBag->getFilesystem()->shouldReceive('exists')->twice()->with('foo')->andReturn(false);
		$themeBag->getFilesystem()->shouldReceive('put')->with('foo', m::type('string'))->twice();
		$locationGenerator->shouldReceive('getPathUrl')->with('foo')->twice()->andReturn('http://example.com/foo.js');

		$this->assertCount(2, $urls = $manager->getCompiledScripts());
		$this->assertEquals(array('http://example.com/foo.js', 'http://example.com/foo.js'), $urls);
	}

	public function testCompilingWhenDebugIsNo()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[getSorted]');
		$manager->__construct(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);
		$manager->setDebug(false);

		$manager->shouldReceive('getSorted')->with('scripts')->once()->andReturn(array(
			$asset1 = new Asset(__FILE__),
			$asset2 = new Asset(__FILE__),
		));

		$manager->addFilter('php', function() { return new FilterStub; });

		$locationGenerator->shouldReceive('getPublicPath')->with('/assets\/cache\/[a-z0-9]+\.js/')->once()->andReturn('foo');
		$themeBag->getFilesystem()->shouldReceive('exists')->once()->with('foo')->andReturn(false);
		$themeBag->getFilesystem()->shouldReceive('put')->with('foo', m::type('string'))->once();
		$locationGenerator->shouldReceive('getPathUrl')->with('foo')->once()->andReturn('http://example.com/foo.js');

		$this->assertCount(1, $urls = $manager->getCompiledScripts());
		$this->assertEquals(array('http://example.com/foo.js'), $urls);
	}

	public function testCompilingWhenForceRecompilingIsEnabled()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[getSorted]');
		$manager->__construct(
			$themeBag          = $this->getMockThemeBag(),
			$viewFinder        = m::mock('Cartalyst\Themes\Views\ViewFinderInterface'),
			$locationGenerator = m::mock('Cartalyst\Themes\Locations\GeneratorInterface')
		);
		$manager->setForceRecompile(true);

		$manager->shouldReceive('getSorted')->with('scripts')->once()->andReturn(array(
			$asset1 = new Asset(__FILE__),
			$asset2 = new Asset(__FILE__),
		));

		$manager->addFilter('php', function() { return new FilterStub; });

		$locationGenerator->shouldReceive('getPublicPath')->with('/assets\/cache\/[a-z0-9]+\.js/')->twice()->andReturn('foo');
		$themeBag->getFilesystem()->shouldReceive('put')->with('foo', m::type('string'))->twice();
		$locationGenerator->shouldReceive('getPathUrl')->with('foo')->twice()->andReturn('http://example.com/foo.js');

		$this->assertCount(2, $urls = $manager->getCompiledScripts());
		$this->assertEquals(array('http://example.com/foo.js', 'http://example.com/foo.js'), $urls);
	}

	/**
	 * Regression test for #7
	 */
	public function testOrderOfAssetsForcesNewCacheKey()
	{
		$manager = m::mock('Cartalyst\Themes\Assets\AssetManager[getCacheKey]');
		$manager->shouldReceive('getCacheKey')->with($asset1 = new Asset(__FILE__))->twice()->andReturn('filters_key_1');
		$manager->shouldReceive('getCacheKey')->with($asset2 = new Asset(__FILE__))->twice()->andReturn('filters_key_2');

		$collection1 = new AssetCollection(array($asset1, $asset2));
		$collection2 = new AssetCollection(array($asset2, $asset1));

		$fileName1 = $manager->getCompileFileName($collection1, 'foo');
		$fileName2 = $manager->getCompileFileName($collection2, 'foo');

		$this->assertNotEquals($fileName1, $fileName2);
	}

	protected function getMockThemeBag()
	{
		$themeBag = m::mock('Cartalyst\Themes\ThemeBag');
		$themeBag->shouldReceive('getFilesystem')->andReturn(m::mock('Illuminate\Filesystem\Filesystem'));
		return $themeBag;
	}

}
