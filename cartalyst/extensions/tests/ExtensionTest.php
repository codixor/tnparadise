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
use Cartalyst\Extensions\Extension;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use PHPUnit_Framework_TestCase;
use stdClass;

class ExtensionTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testExtensionDefaultNamespace()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);
		$extension->ensureNamespace();

		$this->assertEquals('Foo\Bar', $extension->getNamespace());
	}

	public function testExtensionCanHaveCustomNamespace()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(),
			'My\Custom\Namespace'
		);

		$this->assertEquals('My\Custom\Namespace', $extension->getNamespace());
	}

	public function testExtensionReturnsVersion()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'version' => '1.0.0',
			)
		);

		$this->assertEquals('1.0.0', $extension->getVersion());
	}

	public function testExtensionShowsVersionedCorrectly()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$this->assertFalse($extension->isVersioned());
		$extension->version = '1.0.0';
		$this->assertTrue($extension->isVersioned());
	}

	public function testExtensionBootCallackIsCalled()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[setupExtensionContext,isEnabled,registerPackage,getContainer]');
		$extension->shouldReceive('setupExtensionContext')->once();
		$extension->__construct(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'boot' => function(Extension $extension, Container $container)
				{
					$_SERVER['__extension.boot'] = 'success';
				},
			)
		);
		$extension->shouldReceive('isEnabled')->once()->andReturn(true);
		$extension->shouldReceive('registerPackage')->once();

		$extension->shouldReceive('getContainer')->once()->andReturn($container = m::mock('Illuminate\Container\Container'));

		$this->assertFalse($extension->isBooted());
		$extension->boot();

		$this->assertEquals($_SERVER['__extension.boot'], 'success');
		unset($_SERVER['__extension.boot']);

		$this->assertTrue($extension->isBooted());
	}

	public function testComposerAutoloadingDoesNothing()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[registerDefaultAutoloading]');
		$extension->autoload = 'composer';
		$extension->shouldReceive('registerDefaultAutoloading')->never();

		$this->assertNull($extension->registerAutoloading());
		$this->assertEmpty($extension->autoloaders);
	}

	public function testCallBackAutoloadingIsCalled()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[registerDefaultAutoloading]');
		$extension->autoload = function($loader, $extension)
		{
			$loader->addClassMap(array(
				'Baz\Qux' => 'path/to/baz/qux',
			));
			$loader->add('Fred\Corge', 'path/to/fred/corge');
		};
		$extension->shouldReceive('registerDefaultAutoloading')->never();

		$loader = $extension->registerAutoloading();

		$expected = array(
			'Fred\Corge' => array(
				'path/to/fred/corge',
			),
		);

		$this->assertEquals($expected, $loader->getPrefixes());

		$expected = array(
			'Baz\Qux' => 'path/to/baz/qux',
		);

		$this->assertEquals($expected, $loader->getClassMap());
	}

	public function testDefaultAutoloadingActsAsExpected()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);
		$extension->ensureNamespace();

		$loader = $extension->registerDefaultAutoloading();

		$expected = array(
			'Foo\Bar' => array(
				'path/to/extension/src',
			),
		);

		$this->assertEquals($expected, $loader->getPrefixes());
	}

	public function testExtensionReportsInstalledCorrectly()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$this->assertFalse($extension->isInstalled());
		$extension->setDatabaseAttributes(array('version' => '1.0.0'));
		$this->assertTrue($extension->isInstalled());
	}

	public function testExtensionNeedsUpgradeReturnsFalseIfNotVersioned()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isVersioned,isUninstalled]');
		$extension->shouldReceive('isVersioned')->once()->andReturn(false);
		$this->assertFalse($extension->needsUpgrade());
	}

	public function testExtensionNeedsUpgradeReturnsTrueIfNotInstalled()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isVersioned,isUninstalled]');
		$extension->shouldReceive('isVersioned')->once()->andReturn(true);
		$extension->shouldReceive('isUninstalled')->once()->andReturn(true);
		$this->assertTrue($extension->needsUpgrade());
	}

	public function testExtensionNeedsUpgradeComparingVersions()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isVersioned,isUninstalled]');
		$extension->shouldReceive('isVersioned')->times(4)->andReturn(true);
		$extension->shouldReceive('isUninstalled')->times(4)->andReturn(false);

		$extension->setDatabaseAttributes(array('version' => '1.0.0'));
		$extension->version = '1.0.0';
		$this->assertFalse($extension->needsUpgrade());

		$extension->version = '1.0.1';
		$this->assertTrue($extension->needsUpgrade());

		$extension->version = '1.0.1b1';
		$this->assertTrue($extension->needsUpgrade());

		$extension->version = '1.0.1RC1';
		$extension->setDatabaseAttributes(array('version' => '1.0.0b1'));
		$this->assertTrue($extension->needsUpgrade());
	}

	public function testMigrationsPath()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'migrations_path' => 'migrations/path',
			)
		);

		$this->assertEquals('path/to/extension/migrations/path', $extension->getMigrationsPath());

		$extension->migrations_path = 'path: /foo/bar';
		$this->assertEquals('/foo/bar', $extension->getMigrationsPath());
	}

	public function testDatabaseInsert()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[getConnection]');
		$extension->shouldReceive('getConnection')->once()->andReturn($mockConnection = m::mock('stdClass'));

		$mockConnection->shouldReceive('table')->with('extensions')->once()->andReturn($mockConnection);
		$mockConnection->shouldReceive('insert')->with(array(
			'foo' => 'bar',
		))->once();

		$extension->databaseInsert(array('foo' => 'bar'));
		$this->assertEquals(array('foo' => 'bar'), $extension->getDatabaseAttributes());
	}

	public function testDatabaseUpdate()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[getConnection]');
		$extension->__construct(m::mock('Cartalyst\Extensions\ExtensionBag'), 'foo/bar', __DIR__);
		$extension->shouldReceive('getConnection')->once()->andReturn($mockConnection = m::mock('stdClass'));

		$mockConnection->shouldReceive('table')->with('extensions')->once()->andReturn($mockConnection);
		$mockConnection->shouldReceive('where')->with('slug', '=', 'foo/bar')->once()->andReturn($mockConnection);
		$mockConnection->shouldReceive('update')->with(array(
			'foo' => 'bar',
		))->once();

		$extension->databaseUpdate(array('foo' => 'bar'));
		$this->assertEquals(array('foo' => 'bar'), $extension->getDatabaseAttributes());
	}

	public function testDatabaseDelete()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[getConnection]');
		$extension->__construct(m::mock('Cartalyst\Extensions\ExtensionBag'), 'foo/bar', __DIR__);
		$extension->shouldReceive('getConnection')->once()->andReturn($mockConnection = m::mock('stdClass'));

		$mockConnection->shouldReceive('table')->with('extensions')->once()->andReturn($mockConnection);
		$mockConnection->shouldReceive('where')->with('slug', '=', 'foo/bar')->once()->andReturn($mockConnection);
		$mockConnection->shouldReceive('delete')->once();

		$extension->databaseDelete();
		$this->assertEquals(array(), $extension->getDatabaseAttributes());
	}

	public function testDatabaseRefreshWhenAnObjectIsReturned()
	{
		$database = new stdClass;
		$database->foo = 'bar';
		$database->baz = 'bat';

		$extension = m::mock('Cartalyst\Extensions\Extension[getConnection]');
		$extension->__construct(m::mock('Cartalyst\Extensions\ExtensionBag'), 'foo/bar', __DIR__);
		$extension->shouldReceive('getConnection')->once()->andReturn($mockConnection = m::mock('stdClass'));

		$mockConnection->shouldReceive('table')->with('extensions')->once()->andReturn($mockConnection);
		$mockConnection->shouldReceive('where')->with('slug', '=', 'foo/bar')->once()->andReturn($mockConnection);
		$mockConnection->shouldReceive('first')->andReturn($database);

		$attributes = array(
			'foo' => 'bar',
			'baz' => 'bat',
		);

		$extension->hydrateDatabaseAttributes();
		$this->assertEquals($attributes, $extension->getDatabaseAttributes());
	}

	public function testDatabaseRefreshWhenNullIsReturned()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[getConnection]');
		$extension->__construct(m::mock('Cartalyst\Extensions\ExtensionBag'), 'foo/bar', __DIR__);
		$extension->shouldReceive('getConnection')->once()->andReturn($mockConnection = m::mock('stdClass'));

		$mockConnection->shouldReceive('table')->with('extensions')->once()->andReturn($mockConnection);
		$mockConnection->shouldReceive('where')->with('slug', '=', 'foo/bar')->once()->andReturn($mockConnection);
		$mockConnection->shouldReceive('first')->andReturn();

		$extension->hydrateDatabaseAttributes();
		$this->assertEmpty($extension->getDatabaseAttributes());
	}

	public function testInstalling()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[databaseInsert]');
		$extension->__construct(m::mock('Cartalyst\Extensions\ExtensionBag'), 'foo/bar', __DIR__, array(
			'migrations_path' => 'migrations',
			'version'         => '1.0.0',
		));

		Extension::setEventDispatcher($events = new Dispatcher);
		Extension::setMigrator($migrator = m::mock('Illuminate\Database\Migrations\Migrator'));

		$events->listen('extension.installing', function(Extension $extension)
		{
			$_SERVER['__extension.installing'] = true;
		});

		$events->listen('extension.installed', function(Extension $extension)
		{
			$_SERVER['__extension.installed'] = true;
		});

		$migrator->shouldReceive('run')->with($extension->getMigrationsPath())->once();

		$extension->shouldReceive('databaseInsert')->with(array(
			'slug'    => 'foo/bar',
			'version' => '1.0.0',
			'enabled' => false,
		))->once();

		$extension->install();

		$this->assertTrue($_SERVER['__extension.installing']);
		unset($_SERVER['__extension.installing']);
		$this->assertTrue($_SERVER['__extension.installed']);
		unset($_SERVER['__extension.installed']);
	}

	public function testEnabling()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isUninstalled,isEnabled,databaseUpdate]');
		$extension->slug            = 'foo/bar';
		$extension->path            = __DIR__;
		$extension->migrations_path = 'migrations';
		$extension->version         = '1.0.0';

		$extension->shouldReceive('isUninstalled')->once()->andReturn(false);
		$extension->shouldReceive('isEnabled')->once()->andReturn(false);

		$extension->shouldReceive('databaseUpdate')->with(array(
			'enabled' => true,
		))->once();

		Extension::setEventDispatcher($events = new Dispatcher);

		$events->listen('extension.enabling', function(Extension $extension)
		{
			$_SERVER['__extension.enabling'] = true;
		});

		$events->listen('extension.enabled', function(Extension $extension)
		{
			$_SERVER['__extension.enabled'] = true;
		});

		$extension->enable();
		$this->assertTrue($_SERVER['__extension.enabling']);
		unset($_SERVER['__extension.enabling']);
		$this->assertTrue($_SERVER['__extension.enabled']);
		unset($_SERVER['__extension.enabled']);
	}

	public function testUpgrading()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isUninstalled,databaseUpdate]');
		$extension->slug            = 'foo/bar';
		$extension->path            = __DIR__;
		$extension->migrations_path = 'migrations';
		$extension->version         = '1.0.0';

		$extension->shouldReceive('databaseUpdate')->with(array(
			'version' => '1.0.0',
		))->once();

		Extension::setEventDispatcher($events = new Dispatcher);
		Extension::setMigrator($migrator = m::mock('Illuminate\Database\Migrations\Migrator'));

		$events->listen('extension.upgrading', function(Extension $extension)
		{
			$_SERVER['__extension.upgrading'] = true;
		});

		$events->listen('extension.upgraded', function(Extension $extension)
		{
			$_SERVER['__extension.upgraded'] = true;
		});

		$migrator->shouldReceive('run')->with($extension->getMigrationsPath())->once();

		$extension->shouldReceive('isUninstalled')->once()->andReturn(false);

		$extension->upgrade();

		$this->assertTrue($_SERVER['__extension.upgrading']);
		unset($_SERVER['__extension.upgrading']);
		$this->assertTrue($_SERVER['__extension.upgraded']);
		unset($_SERVER['__extension.upgraded']);
	}

	public function testSettingUpRoutes()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isBooted,getContainer]');
		$extension->routes = function(Extension $extension, Container $container)
		{
			$_SERVER['__extension.routes'] = 'success';
		};

		$extension->shouldReceive('isBooted')->once()->andReturn(true);
		$extension->shouldReceive('getContainer')->once()->andReturn($container = m::mock('Illuminate\Container\Container'));

		Extension::setEventDispatcher($events = new Dispatcher);

		$extension->setupRoutes();
		$this->assertEquals('success', $_SERVER['__extension.routes']);
		unset($_SERVER['__extension.routes']);
	}

	public function testExtensionRegisterCallackIsCalled()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[setupExtensionContext,registerAutoloading,getContainer]');
		$extension->shouldReceive('setupExtensionContext')->once();
		$extension->__construct(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'register' => function(Extension $extension, Container $container)
				{
					$_SERVER['__extension.register'] = 'success';
				},
			)
		);

		$extension->shouldReceive('registerAutoloading')->once();
		$extension->shouldReceive('getContainer')->once()->andReturn($container = m::mock('Illuminate\Container\Container'));

		$this->assertFalse($extension->isRegistered());

		$extension->register();

		$this->assertEquals($_SERVER['__extension.register'], 'success');
		unset($_SERVER['__extension.register']);

		$this->assertTrue($extension->isRegistered());
	}

	public function testRegisteringPackageDoesNotFailWithoutAContainer()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[setupExtensionContext,registerAutoloading,getContainer]');
		$extension->shouldReceive('setupExtensionContext')->once();
		$extension->__construct(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$extension->shouldReceive('getContainer')->once();

		$extension->registerPackage();
	}

	public function testRegisteringPackageWorksAsExpected()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[setupExtensionContext,registerAutoloading,getContainer]');
		$extension->shouldReceive('setupExtensionContext')->once();
		$extension->__construct(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$extension->shouldReceive('getContainer')->once()->andReturn($container = new Container);

		$container['files'] = $files = m::mock('Illuminate\Filesystem\Filesystem');
		$files->shouldReceive('isDirectory')->with('path/to/extension/config')->once()->andReturn(true);
		$container['config'] = $config = m::mock('Illuminate\Config\Repository');
		$config->shouldReceive('package')->with('foo/bar', 'path/to/extension/config', 'foo/bar')->once();

		$files->shouldReceive('isDirectory')->with('path/to/extension/lang')->once()->andReturn(true);
		$container['translator'] = $translator = m::mock('Illuminate\Translation\Translator');
		$translator->shouldReceive('addNamespace')->with('foo/bar', 'path/to/extension/lang')->once();

		$files->shouldReceive('isDirectory')->with('path/to/extension/views')->once()->andReturn(true);
		$container['view'] = $view = m::mock('Illuminate\View\Environment');
		$view->shouldReceive('addNamespace')->with('foo/bar', 'path/to/extension/views')->once();

		$extension->registerPackage();
	}

	public function testExtensionKnowsIfItCanInstallWithNoDependencies()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$this->assertTrue($extension->canInstall());
	}

	public function testExtensionKnowsIfItCanInstallWithDependenciesNotRegistered()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'require' => array('qux/corge'),
			)
		);

		$bag->shouldReceive('offsetExists')->with('qux/corge')->once();

		$this->assertFalse($extension->canInstall());
	}

	public function testExtensionKnowsIfItCanInstallWhenOneDepenencyIsNotInstalled()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'require' => array(
					'baz/bat',
					'qux/corge',
				),
			)
		);

		$bag->shouldReceive('offsetExists')->with('baz/bat')->once()->andReturn(true);
		$bag->shouldReceive('offsetExists')->with('qux/corge')->once()->andReturn(true);
		$bag->shouldReceive('offsetGet')->with('baz/bat')->once()->andReturn($extension1 = m::mock('Cartalyst\Extensions\Extension'));
		$bag->shouldReceive('offsetGet')->with('qux/corge')->once()->andReturn($extension2 = m::mock('Cartalyst\Extensions\Extension'));
		$extension1->shouldReceive('isInstalled')->once()->andReturn(true);
		$extension2->shouldReceive('isInstalled')->once()->andReturn(false);

		$this->assertFalse($extension->canInstall());
	}

	public function testExtensionKnowsIfItCanInstall()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'require' => array(
					'baz/bat',
					'qux/corge',
				),
			)
		);

		$bag->shouldReceive('offsetExists')->with('baz/bat')->once()->andReturn(true);
		$bag->shouldReceive('offsetExists')->with('qux/corge')->once()->andReturn(true);
		$bag->shouldReceive('offsetGet')->with('baz/bat')->once()->andReturn($extension1 = m::mock('Cartalyst\Extensions\Extension'));
		$bag->shouldReceive('offsetGet')->with('qux/corge')->once()->andReturn($extension2 = m::mock('Cartalyst\Extensions\Extension'));
		$extension1->shouldReceive('isInstalled')->once()->andReturn(true);
		$extension2->shouldReceive('isInstalled')->once()->andReturn(true);

		$this->assertTrue($extension->canInstall());
	}

	public function testExtensionKnowsIfItCanBeUninstalledWhenItIsADependency()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$bag->shouldReceive('allInstalled')->once()->andReturn(array(
			$extension1 = m::mock('Cartalyst\Extensions\Extension'),
			$extension2 = m::mock('Cartalyst\Extensions\Extension'),
		));

		$extension1->shouldReceive('getDependencies')->once()->andReturn(array());
		$extension2->shouldReceive('getDependencies')->once()->andReturn(array('foo/bar'));

		$this->assertFalse($extension->canUninstall());
	}

	public function testExtensionKnowsIfItCanBeUninstalled()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$bag->shouldReceive('allInstalled')->once()->andReturn(array(
			$extension1 = m::mock('Cartalyst\Extensions\Extension'),
			$extension2 = m::mock('Cartalyst\Extensions\Extension'),
		));

		$extension1->shouldReceive('getDependencies')->once()->andReturn(array());
		$extension2->shouldReceive('getDependencies')->once()->andReturn(array('not_foo/bar'));

		$this->assertTrue($extension->canUninstall());
	}

	public function testExtensionReportsUninstalledCorrectly()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isInstalled]');
		$extension->shouldReceive('isInstalled')->once()->andReturn(false);
		$this->assertTrue($extension->isUninstalled());

		$extension = m::mock('Cartalyst\Extensions\Extension[isInstalled]');
		$extension->shouldReceive('isInstalled')->once()->andReturn(true);
		$this->assertFalse($extension->isUninstalled());
	}

	public function testExtensionKnowsIfItCanEnableWithNoDependencies()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$this->assertTrue($extension->canEnable());
	}

	public function testExtensionKnowsIfItCanEnableWithDependenciesNotRegistered()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'require' => array('qux/corge'),
			)
		);

		$bag->shouldReceive('offsetExists')->with('qux/corge')->once();

		$this->assertFalse($extension->canEnable());
	}

	public function testExtensionKnowsIfItCanEnableWhenOneDepenencyIsNotEnabled()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'require' => array(
					'baz/bat',
					'qux/corge',
				),
			)
		);

		$bag->shouldReceive('offsetExists')->with('baz/bat')->once()->andReturn(true);
		$bag->shouldReceive('offsetExists')->with('qux/corge')->once()->andReturn(true);
		$bag->shouldReceive('offsetGet')->with('baz/bat')->once()->andReturn($extension1 = m::mock('Cartalyst\Extensions\Extension'));
		$bag->shouldReceive('offsetGet')->with('qux/corge')->once()->andReturn($extension2 = m::mock('Cartalyst\Extensions\Extension'));
		$extension1->shouldReceive('isEnabled')->once()->andReturn(true);
		$extension2->shouldReceive('isEnabled')->once()->andReturn(false);

		$this->assertFalse($extension->canEnable());
	}

	public function testExtensionKnowsIfItCanEnable()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'require' => array(
					'baz/bat',
					'qux/corge',
				),
			)
		);

		$bag->shouldReceive('offsetExists')->with('baz/bat')->once()->andReturn(true);
		$bag->shouldReceive('offsetExists')->with('qux/corge')->once()->andReturn(true);
		$bag->shouldReceive('offsetGet')->with('baz/bat')->once()->andReturn($extension1 = m::mock('Cartalyst\Extensions\Extension'));
		$bag->shouldReceive('offsetGet')->with('qux/corge')->once()->andReturn($extension2 = m::mock('Cartalyst\Extensions\Extension'));
		$extension1->shouldReceive('isEnabled')->once()->andReturn(true);
		$extension2->shouldReceive('isEnabled')->once()->andReturn(true);

		$this->assertTrue($extension->canEnable());
	}

	public function testExtensionKnowsIfItCanBeDisabledWhenItIsADependency()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$bag->shouldReceive('allEnabled')->once()->andReturn(array(
			$extension1 = m::mock('Cartalyst\Extensions\Extension'),
			$extension2 = m::mock('Cartalyst\Extensions\Extension'),
		));

		$extension1->shouldReceive('getDependencies')->once()->andReturn(array());
		$extension2->shouldReceive('getDependencies')->once()->andReturn(array('foo/bar'));

		$this->assertFalse($extension->canDisable());
	}

	public function testExtensionKnowsIfItCanBeDisabled()
	{
		$extension = new Extension(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension'
		);

		$bag->shouldReceive('allEnabled')->once()->andReturn(array(
			$extension1 = m::mock('Cartalyst\Extensions\Extension'),
			$extension2 = m::mock('Cartalyst\Extensions\Extension'),
		));

		$extension1->shouldReceive('getDependencies')->once()->andReturn(array());
		$extension2->shouldReceive('getDependencies')->once()->andReturn(array('not_foo/bar'));

		$this->assertTrue($extension->canDisable());
	}

	public function testExtensionReportsDisabledCorrectly()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isEnabled]');
		$extension->shouldReceive('isEnabled')->once()->andReturn(false);
		$this->assertTrue($extension->isDisabled());

		$extension = m::mock('Cartalyst\Extensions\Extension[isEnabled]');
		$extension->shouldReceive('isEnabled')->once()->andReturn(true);
		$this->assertFalse($extension->isDisabled());
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testDisablingWhenNotEnabled()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isUninstalled,isDisabled,databaseUpdate]');
		$extension->slug            = 'foo/bar';
		$extension->path            = __DIR__;
		$extension->migrations_path = 'migrations';
		$extension->version         = '1.0.0';

		$extension->shouldReceive('isUninstalled')->once()->andReturn(false);
		$extension->shouldReceive('isDisabled')->once()->andReturn(true);

		$extension->disable();
	}

	public function testDisabling()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isUninstalled,isDisabled,databaseUpdate]');
		$extension->slug            = 'foo/bar';
		$extension->path            = __DIR__;
		$extension->migrations_path = 'migrations';
		$extension->version         = '1.0.0';

		$extension->shouldReceive('isUninstalled')->once()->andReturn(false);
		$extension->shouldReceive('isDisabled')->once()->andReturn(false);

		$extension->shouldReceive('databaseUpdate')->with(array(
			'enabled' => false,
		))->once();

		Extension::setEventDispatcher($events = new Dispatcher);

		$events->listen('extension.disabling', function(Extension $extension)
		{
			$_SERVER['__extension.disabling'] = true;
		});

		$events->listen('extension.disabled', function(Extension $extension)
		{
			$_SERVER['__extension.disabled'] = true;
		});

		$extension->disable();

		$this->assertTrue($_SERVER['__extension.disabling']);
		unset($_SERVER['__extension.disabling']);
		$this->assertTrue($_SERVER['__extension.disabled']);
		unset($_SERVER['__extension.disabled']);
	}

	public function testUninstalling()
	{
		$extension = m::mock('Cartalyst\Extensions\Extension[isUninstalled,databaseDelete]');
		$extension->__construct(
			$bag = m::mock('Cartalyst\Extensions\ExtensionBag'),
			'foo/bar',
			'path/to/extension',
			array(
				'migrations_path' => 'migrations',
			)
		);

		Extension::setMigrator($migrator = m::mock('Illuminate\Database\Migrations\Migrator'));

		$extension->shouldReceive('isUninstalled')->once();

		$migrator->shouldReceive('getMigrationFiles')->with('path/to/extension/migrations')->once()->andReturn($files = array(
			'migration_foo',
			'migration_bar',
		));

		$migrator->shouldReceive('getRepository')->once()->andReturn($repository = m::mock('Illuminate\Database\Migrations\MigrationRepositoryInterface'));

		$repository->shouldReceive('getRan')->once()->andReturn($ran = array(
			'migration_foo',
			'migration_bar',
			'migration_baz',
		));

		// Test we are resolving in reverse order
		$migrator->shouldReceive('resolve')->with('migration_bar')->once()->ordered()->andReturn($migration2 = m::mock('stdClass'));
		$migration2->shouldReceive('down')->once()->ordered();

		$migrator->shouldReceive('resolve')->with('migration_foo')->once()->ordered()->andReturn($migration1 = m::mock('stdClass'));
		$migration1->shouldReceive('down')->once()->ordered();

		$repository->shouldReceive('delete')->with(m::type('stdClass'))->twice();

		$extension->shouldReceive('databaseDelete')->once();

		Extension::setEventDispatcher($events = new Dispatcher);

		$events->listen('extension.uninstalling', function(Extension $extension)
		{
			$_SERVER['__extension.uninstalling'] = true;
		});

		$events->listen('extension.uninstalled', function(Extension $extension)
		{
			$_SERVER['__extension.uninstalled'] = true;
		});

		$extension->uninstall();

		$this->assertTrue($_SERVER['__extension.uninstalling']);
		unset($_SERVER['__extension.uninstalling']);
		$this->assertTrue($_SERVER['__extension.uninstalled']);
		unset($_SERVER['__extension.uninstalled']);
	}

}
