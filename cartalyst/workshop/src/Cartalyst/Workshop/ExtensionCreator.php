<?php namespace Cartalyst\Workshop;
/**
 * Part of the Workshop package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Workshop
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Workbench\PackageCreator;
use Illuminate\Workbench\Package;

class ExtensionCreator extends PackageCreator {

	protected $components = array(
		'config',
		'widget',
		'admin',
		'frontend',
	);

	/**
	 * The building blocks of the package.
	 *
	 * @param  array
	 */
	protected $blocks = array(
		'SupportFiles',
		'ControllersDirectory',
		'ThemeDirectories',
		'TestDirectory',
		'DatabaseDirectories',
		// 'LanguageFiles',
		'ExtensionFile',
	);

	protected $configBlocks = array(
		'ConfigFile',
	);

	protected $widgetBlocks = array(
		'WidgetClass',
	);

	protected $adminBlocks = array(
		// 'AdminLanguageFile',
		'AdminController',
		'AdminThemeView',
		'AdminThemeCss',
		'AdminThemeJs',
		// 'ControllerFile',
		// 'PermissionsFile',
		// 'ThemeFile',
	);

	protected $frontendBlocks = array(
		'FrontendController',
	);

	protected $classmapAutoloads = array();

	/**
	 * Create a new package stub.
	 *
	 * @param  \Illuminate\Workbench\Package  $repository
	 * @param  string  $path
	 * @param  array    $components
	 * @return string
	 */
	public function create(Package $repository, $path, $components = true)
	{
		$this->checkRepository($repository);

		if ($components === true)
		{
			$components = $this->components;
		}
		else
		{
			foreach ($components as $component)
			{
				if ( ! in_array($component, $this->components))
				{
					throw new \InvalidArgumentException("Component [$component] is not a valid component of an Extension to create.");
				}
			}
		}

		$directory = $this->createDirectory($repository, $path);

		foreach ($this->blocks as $block)
		{
			$this->{"write{$block}"}($repository, $directory, false);
		}

		foreach ($components as $component)
		{
			foreach ($this->{$component.'Blocks'} as $block)
			{
				$this->{"write{$block}"}($repository, $directory);
			}
			// $this->{'create'.studly_case($component).'Component'}($repository, $path);
		}

		$this->writeComposerFile($repository, $directory, false);
		$this->writeRootWorkbenchComposerFileAddons($repository, $directory);
		$this->writeRootExtensionsComposerFileAddons($repository, $directory);

		return $directory;
	}

	/**
	 * Write the support files to the package root.
	 *
	 * @param  \Illuminate\Workbench\Package  $package
	 * @param  string  $directory
	 * @return void
	 */
	public function writeSupportFiles(Package $package, $directory, $plain)
	{
		foreach (array('PhpUnit', 'Travis', 'Ignore') as $file)
		{
			$this->{"write{$file}File"}($package, $directory, $plain);
		}
	}

	/**
	 * Create the controllers directory for the package.
	 *
	 * @param  \Illuminate\Workbench\Package  $package
	 * @param  string  $directory
	 * @return void
	 */
	public function writeControllersDirectory(Repository $repository, $directory)
	{
		$this->files->makeDirectory($directory.'/controllers');

		$this->files->put($directory.'/controllers/.gitkeep', '');
	}

	/**
	 * Create the themes directory for the package.
	 *
	 * @param  \Illuminate\Workbench\Package  $package
	 * @param  string  $directory
	 * @return void
	 */
	public function writeThemeDirectories(Package $package, $directory)
	{
		$this->files->makeDirectory($directory.'/themes/admin', 0777, true);
		$this->files->makeDirectory($directory.'/themes/frontend', 0777, true);

		$this->files->put($directory.'/themes/admin/.gitkeep', '');
		$this->files->put($directory.'/themes/frontend/.gitkeep', '');
	}

	/**
	 * Create the themes directory for the package.
	 *
	 * @param  \Illuminate\Workbench\Package  $package
	 * @param  string  $directory
	 * @return void
	 */
	public function writeDatabaseDirectories(Package $package, $directory)
	{
		$this->files->makeDirectory($directory.'/migrations', 0777, true);

		$this->files->put($directory.'/migrations/.gitkeep', '');

		$this->ensureClassMapAutoload('migrations');
	}

	protected function writeExtensionFile(Repository $repository, $directory)
	{
		$stub = $this->getExtensionStub();

		$stub = $this->formatPackageStub($repository, $stub);

		$this->files->put($directory.'/extension.php', $stub);
	}

	protected function getExtensionStub()
	{
		return $this->files->get(__DIR__.'/stubs/extension.stub');
	}

	protected function writeConfigFile(Repository $repository, $directory)
	{
		$this->files->makeDirectory($directory.'/config');

		$stub = $this->getConfigStub();

		$stub = $this->formatPackageStub($repository, $stub);

		$this->files->put($directory.'/config/config.php', $stub);
	}

	protected function getConfigStub()
	{
		return $this->files->get(__DIR__.'/stubs/config/config.stub');
	}

	protected function writeWidgetClass(Repository $repository, $directory)
	{
		$widgetsDirectory = $directory.'/widgets';

		$this->files->makeDirectory($widgetsDirectory);

		$stub = $this->getWidgetStub();

		$stub = $this->formatPackageStub($repository, $stub);

		$this->files->put($widgetsDirectory.'/'.'Main.php', $stub);

		$this->ensureClassMapAutoload('widgets');
	}

	protected function getWidgetStub()
	{
		return $this->files->get(__DIR__.'/stubs/widget.stub');
	}

	protected function writeAdminController(Repository $repository, $directory)
	{
		$stub = $this->getAdminControllerStub();

		$stub = $this->formatPackageStub($repository, $stub);

		$path = $this->createControllerDirectory($repository, $directory, 'admin');

		$this->files->put($path.'/'.$repository->name.'Controller.php', $stub);

		$this->ensureClassMapAutoload('controllers');
	}

	protected function ensureClassMapAutoload($path)
	{
		if ( ! in_array($path, $this->classmapAutoloads))
		{
			$this->classmapAutoloads[] = $path;
		}
	}

	protected function getAdminControllerStub()
	{
		return $this->files->get(__DIR__.'/stubs/admin/controller.stub');
	}

	protected function writeFrontendController(Repository $repository, $directory)
	{
		$stub = $this->getFrontendControllerStub();

		$stub = $this->formatPackageStub($repository, $stub);

		$path = $this->createControllerDirectory($repository, $directory, 'frontend');

		$this->files->put($path.'/'.$repository->name.'Controller.php', $stub);
	}

	protected function getFrontendControllerStub()
	{
		return $this->files->get(__DIR__.'/stubs/frontend/controller.stub');
	}

	protected function createControllerDirectory(Repository $repository, $directory, $controllerPath = null)
	{
		$path = $directory.'/controllers';
		if ($controllerPath)
		{
			$controllerPath = implode('/', array_map(function($segment)
			{
				return studly_case($segment);
			}, explode('/', $controllerPath)));
			$path .= '/'.$controllerPath;
		}

		if ( ! $this->files->isDirectory($path))
		{
			$this->files->makeDirectory($path, 0777, true);
		}

		return $path;
	}

	protected function writeAdminThemeView(Repository $repository, $directory)
	{
		$stub = $this->getAdminThemeViewStub();

		$stub = $this->formatPackageStub($repository, $stub);

		$path = $this->createThemeDirectory($repository, $directory, 'admin', 'views');

		$this->files->put($path.'/index.blade.php', $stub);
	}

	protected function getAdminThemeViewStub()
	{
		return $this->files->get(__DIR__.'/stubs/admin/index.blade.stub');
	}

	protected function writeAdminThemeCss(Repository $repository, $directory)
	{
		$stub = $this->getAdminThemeViewCss();

		$stub = $this->formatPackageStub($repository, $stub);

		$path = $this->createThemeDirectory($repository, $directory, 'admin', 'assets/css');

		$this->files->put($path.'/style.css', $stub);
	}

	protected function getAdminThemeViewCss()
	{
		return $this->files->get(__DIR__.'/stubs/admin/style.css');
	}

	protected function writeAdminThemeJs(Repository $repository, $directory)
	{
		$stub = $this->getAdminThemeViewJs();

		$stub = $this->formatPackageStub($repository, $stub);

		$path = $this->createThemeDirectory($repository, $directory, 'admin', 'assets/js');

		$this->files->put($path.'/script.js', $stub);
	}

	protected function getAdminThemeViewJs()
	{
		return $this->files->get(__DIR__.'/stubs/admin/script.js');
	}

	protected function createThemeDirectory(Repository $repository, $directory, $area, $subPath = null)
	{
		$path = $directory.'/themes/admin/default/packages/'.$repository->lowerVendor.'/'.$repository->lowerName;

		if ($subPath)
		{
			$path .= '/'.$subPath;
		}

		if ( ! $this->files->isDirectory($path))
		{
			$this->files->makeDirectory($path, 0777, true);
		}

		return $path;
	}

	/**
	 * Format a generic package stub file.
	 *
	 * @param  \Illuminate\Workbench\Package  $repository
	 * @param  string  $stub
	 * @return string
	 */
	protected function formatPackageStub(Package $repository, $stub)
	{
		$this->checkRepository($repository);

		foreach ($repository->getFormattedAttributes() as $key => $value)
		{
			$stub = str_replace('{{'.snake_case($key).'}}', $value, $stub);
		}

		return $stub;
	}

	/**
	 * Write the Composer.json stub file.
	 *
	 * @param  \Illuminate\Workbench\Package  $package
	 * @param  string  $directory
	 * @return void
	 */
	protected function writeComposerFile(Package $repository, $directory, $plain)
	{
		$this->checkRepository($repository);

		$stub = $this->getComposerStub(false);

		$stub = $this->formatPackageStub($repository, $stub);

		$stub = $this->injectAutoloads($stub);

		$this->files->put($directory.'/composer.json', $stub);
	}

	/**
	 * Get the Composer.json stub file contents.
	 *
	 * @param  bool    $plain
	 * @return string
	 */
	protected function getComposerStub($plain)
	{
		if ($plain) return $this->files->get(__DIR__.'/stubs/plain.composer.json');

		return $this->files->get(__DIR__.'/stubs/composer.json');
	}

	/**
	 * Write the Composer.json stub file.
	 *
	 * @param  \Illuminate\Workbench\Package  $package
	 * @param  string  $directory
	 * @return void
	 */
	protected function writeRootWorkbenchComposerFileAddons(Package $repository, $directory)
	{
		$this->checkRepository($repository);

		$stub = $this->getRootWorkbenchComposerStub();

		$stub = $this->formatPackageStub($repository, $stub);

		$stub = $this->injectAutoloads($stub, 'workbench/'.$repository->lowerVendor.'/'.$repository->lowerName);

		$this->files->put($directory.'/root.workbench.composer.json', $stub);
	}

	/**
	 * Write the Composer.json stub file.
	 *
	 * @param  \Illuminate\Workbench\Package  $package
	 * @param  string  $directory
	 * @return void
	 */
	protected function writeRootExtensionsComposerFileAddons(Package $repository, $directory)
	{
		$this->checkRepository($repository);

		$stub = $this->getRootExtensionsComposerStub();

		$stub = $this->formatPackageStub($repository, $stub);

		$stub = $this->injectAutoloads($stub, 'extensions/'.$repository->lowerVendor.'/'.$repository->lowerName);

		$this->files->put($directory.'/root.extensions.composer.json', $stub);
	}

	protected function injectAutoloads($stub, $prefix = null)
	{
		$autoloads = $this->classmapAutoloads;
		sort($autoloads);

		return str_replace('{{classmap_autoloads}}', implode(",\n\t\t\t", array_map(function($autoload) use ($prefix)
		{
			return '"'.($prefix ? $prefix.'/' : '').$autoload.'"';
		}, $autoloads)), $stub);
	}

	/**
	 * Get the Composer.json stub file contents.
	 *
	 * @return string
	 */
	protected function getRootWorkbenchComposerStub()
	{
		return $this->files->get(__DIR__.'/stubs/root.workbench.composer.json');
	}

	/**
	 * Get the Composer.json stub file contents.
	 *
	 * @return string
	 */
	protected function getRootExtensionsComposerStub()
	{
		return $this->files->get(__DIR__.'/stubs/root.extensions.composer.json');
	}

	protected function checkRepository(Repository $repository) {}

}
