<?php namespace Cartalyst\Extensions;
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

use Closure;
use Composer\Autoload\ClassLoader;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Contracts\ArrayableInterface;

class Extension implements ArrayableInterface, ExtensionInterface {

	/**
	 * Indicates if the application has "registered" with the bag.
	 *
	 * @var bool
	 */
	protected $registered = false;

	/**
	 * Indicates if the application has "booted".
	 *
	 * @var bool
	 */
	protected $booted = false;

	/**
	 * Extension bag instance.
	 *
	 * @var Cartalyst\Extensions\ExtensionBag
	 */
	protected $extensionBag;

	/**
	 * The Extension's slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The Extension's path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * The extension's attributes.
	 *
	 * @var array
	 */
	protected $attributes = array(
		'migrations_path' => 'migrations',
	);

	/**
	 * The extension's database attributes.
	 *
	 * @var array
	 */
	protected $databaseAttributes = array();

	/**
	 * The Extension's namespace.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * The array of autoloaders registered with the extension.
	 *
	 * @var array
	 */
	protected $autoloaders = array();

	/**
	 * The connection name for the extension.
	 *
	 * @var string
	 */
	protected $connection;

	/**
	 * The connection resolver instance.
	 *
	 * @var Illuminate\Database\ConnectionResolverInterface
	 */
	protected static $resolver;

	/**
	 * The event dispatcher instance.
	 *
	 * @var Illuminate\Events\Dispacher
	 */
	protected static $dispatcher;

	/**
	 * Extension migrator.
	 *
	 * @var Illuminate\Database\Migrations\Migrator
	 */
	protected static $migrator;

	/**
	 * Create a new extension instance.
	 *
	 * @param  Cartalyst\Extensions\ExtensionBag  $extensionBag
	 * @param  string  $slug
	 * @param  string  $path
	 * @param  array   $attributes
	 * @param  string  $namespace
	 * @return void
	 */
	public function __construct(ExtensionBag $extensionBag, $slug, $path, array $attributes = array(), $namespace = null)
	{
		$this->extensionBag = $extensionBag;
		$this->slug         = $slug;
		$this->path         = $path;
		$this->fill($attributes);

		// If we have a namespace provided, we will register that now
		if (isset($namespace))
		{
			$this->namespace = $namespace;
		}

		$this->setupExtensionContext();
	}

	/**
	 * Returns the extension's slug.
	 *
	 * @return string
	 */
	public function getSlug()
	{
		return $this->slug;
	}

	/**
	 * Returns the extension's path.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Returns the extension's namespace.
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * Return the extension's vendor name.
	 *
	 * @return string
	 */
	public function getVendor()
	{
		list($vendor) = explode('\\', $this->namespace);

		return $vendor;
	}

	/**
	 * Returns all dependencies (array
	 * of extension slugs) for the
	 * extension.
	 *
	 * @return array
	 */
	public function getDependencies()
	{
		if (isset($this->require))
		{
			return (array) $this->require;
		}

		return array();
	}

	/**
	 * Returns if the extension is versioned
	 * or not.
	 *
	 * @return bool
	 */
	public function isVersioned()
	{
		return (bool) $this->getVersion();
	}

	/**
	 * Returns the extension's version.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		if (isset($this->version))
		{
			return $this->version;
		}
	}

	/**
	 * Returns if an extension can be installed and a
	 * number of exceptions if it cannot.
	 *
	 * @return bool
	 */
	public function canInstall()
	{
		// If we have no dependencies, we can install
		if ( ! $dependencies = $this->getDependencies())
		{
			return true;
		}

		// Loop through dependencies and check they are
		// installed
		foreach ($dependencies as $dependency)
		{
			if ( ! isset($this->extensionBag[$dependency]))
			{
				return false;
			}

			if ( ! $this->extensionBag[$dependency]->isInstalled())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns if an Extension is installed.
	 *
	 * @return bool
	 */
	public function isInstalled()
	{
		// If we have no database attributes present, we're not
		// installed
		if ( ! count($this->databaseAttributes))
		{
			return false;
		}

		return isset($this->databaseAttributes['version']);
	}

	/**
	 * Installs the Extension by running migrations
	 * and calling events which can be hooked into,
	 * for example used to update database attributes.
	 *
	 * @return void
	 */
	public function install()
	{
		if ($this->isInstalled())
		{
			throw new \RuntimeException("Cannot install Extension [{$this->slug}] as it is not installed.");
		}

		$this->fireEvent('installing');

		$this->migrate();

		$this->databaseInsert(array(
			'slug'    => $this->slug,
			'version' => $this->version,
			'enabled' => false,
		));

		$this->fireEvent('installed');
	}

	/**
	 * Returns if an extension can be uninstalled and a
	 * number of exceptions if it cannot.
	 *
	 * @return bool
	 */
	public function canUninstall()
	{
		// Loop through all installed extensions and check
		// we are not a dependency
		foreach ($this->extensionBag->allInstalled() as $extension)
		{
			if (in_array($this->slug, $extension->getDependencies()))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns if the Extension is uninstalled.
	 *
	 * @return bool
	 */
	public function isUninstalled()
	{
		return ( ! $this->isInstalled());
	}

	/**
	 * Uninstalls the Extension by running migrations
	 * and calling events which can be hooked into,
	 * for example used to update database attributes.
	 *
	 * @return void
	 */
	public function uninstall()
	{
		if ($this->isUninstalled())
		{
			throw new \RuntimeException("Cannot enable Extension [{$this->slug}] as it is not installed.");
		}

		$this->fireEvent('uninstalling');

		$this->resetMigrations();

		$this->databaseDelete();

		$this->fireEvent('uninstalled');
	}

	/**
	 * Returns if an extension can be enabled and a
	 * number of exceptions if it cannot.
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public function canEnable()
	{
		// If we have no dependencies, we can enable
		if ( ! $dependencies = $this->getDependencies())
		{
			return true;
		}

		// Loop through dependencies and check they are
		// enabled
		foreach ($dependencies as $dependency)
		{
			if ( ! isset($this->extensionBag[$dependency]))
			{
				return false;
			}

			if ( ! $this->extensionBag[$dependency]->isEnabled())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns whether an Extension is enabled or not.
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if ( ! $this->isInstalled())
		{
			return false;
		}

		if ( ! isset($this->databaseAttributes['enabled']))
		{
			return false;
		}

		return (bool) $this->databaseAttributes['enabled'];
	}

	/**
	 * Enables the extension.
	 *
	 * @return void
	 */
	public function enable()
	{
		if ($this->isUninstalled())
		{
			throw new \RuntimeException("Cannot enable Extension [{$this->slug}] as it is not installed.");
		}
		if ($this->isEnabled())
		{
			throw new \RuntimeException("Cannot enable Extension [{$this->slug}] as it is not enabled.");
		}

		$this->fireEvent('enabling');

		$this->databaseUpdate(array(
			'enabled' => true,
		));

		$this->fireEvent('enabled');
	}

	/**
	 * Returns if the extension can be disabled.
	 *
	 * @return bool
	 */
	public function canDisable()
	{
		// Loop through all installed extensions and check
		// we are not a dependency
		foreach ($this->extensionBag->allEnabled() as $extension)
		{
			if (in_array($this->slug, $extension->getDependencies()))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns if the extension is disabled.
	 *
	 * @return bool
	 */
	public function isDisabled()
	{
		return ( ! $this->isEnabled());
	}

	/**
	 * Disables the extension.
	 *
	 * @return void
	 */
	public function disable()
	{
		if ($this->isUninstalled())
		{
			throw new \RuntimeException("Cannot disable Extension [{$this->slug}] as it is not installed.");
		}
		if ($this->isDisabled())
		{
			throw new \RuntimeException("Cannot disable Extension [{$this->slug}] as it is not enabled.");
		}

		$this->fireEvent('disabling');

		$this->databaseUpdate(array(
			'enabled' => false,
		));

		$this->fireEvent('disabled');
	}

	/**
	 * Returns whether an Extension needs upgrades or not.
	 *
	 * @return bool
	 */
	public function needsUpgrade()
	{
		// Not versioned?
		if ( ! $this->isVersioned())
		{
			return false;
		}

		// No database version? We haven't been
		// persisted in the database yet
		if ($this->isUninstalled())
		{
			return true;
		}

		return (version_compare($this->version, $this->databaseAttributes['version']) > 0);
	}

	/**
	 * Upgrades the Extension by running migrations
	 * and calling events which can be hooked into,
	 * for example used to update database attributes.
	 *
	 * @return void
	 */
	public function upgrade()
	{
		if ($this->isUninstalled())
		{
			throw new \RuntimeException("Cannot upgrade Extension [{$this->slug}] as it is not installed.");
		}

		$this->fireEvent('upgrading');

		$this->migrate();

		$this->databaseUpdate(array(
			'version' => $this->version,
		));

		$this->fireEvent('upgraded');
	}

	/**
	 * Returns if the extension is registered.
	 *
	 * @return bool
	 */
	public function isRegistered()
	{
		return $this->registered;
	}

	/**
	 * Registers the extension. Called when added to the extension
	 * bag. All extensions should be registered before any are
	 * booted.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->fireEvent('registering');

		if (isset($this->register) and $this->register instanceof Closure)
		{
			$container = $this->getContainer();

			call_user_func_array($this->register, array($this, $container));
		}

		$this->registered = true;

		$this->registerAutoloading();

		$this->fireEvent('registered');
	}

	/**
	 * Returns if the extension is booted.
	 *
	 * @return bool
	 */
	public function isBooted()
	{
		return $this->booted;
	}

	/**
	 * Boots the extension.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ( ! $this->isEnabled())
		{
			throw new \RuntimeException("Cannot boot Extension [{$this->slug}] as it is not enabled.");
		}

		// We will register a package right away
		// before we fire our events as this allows
		// all callback to hook onto the registered
		// package.
		$this->registerPackage();

		$this->fireEvent('booting');

		if (isset($this->boot) and $this->boot instanceof Closure)
		{
			$container = $this->getContainer();

			call_user_func_array($this->boot, array($this, $container));
		}

		$this->booted = true;

		$this->setupRoutes();

		$this->fireEvent('booted');
	}

	/**
	 * Sets up the extension context, ready for use.
	 *
	 * @return void
	 */
	public function setupExtensionContext()
	{
		$this->ensureNamespace();
	}

	/**
	 * Sets up the database with the extension.
	 *
	 * @return void
	 */
	public function setupDatabase()
	{
		$this->hydrateDatabaseAttributes();
	}

	/**
	 * Returns the migrations path for the extension.
	 * An absolute path can be specified by prefixing
	 * the path with a string 'path: ', otherwise the
	 * path is treated as relative to the extension's
	 * path.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public function getMigrationsPath()
	{
		if (starts_with($this->migrations_path, 'path: '))
		{
			return substr($this->migrations_path, 6);
		}

		return $this->path.'/'.$this->migrations_path;
	}

	/**
	 * Ensures we have a namespace and if not, sets the
	 * default namespace.
	 *
	 * @return void
	 */
	public function ensureNamespace()
	{
		if ( ! isset($this->namespace))
		{
			$this->namespace = str_replace(' ', '\\', ucwords(str_replace('/', ' ', $this->slug)));
		}
	}

	/**
	 * Registers autoloading for the extension.
	 *
	 * @return Composer\Autoload\ClassLoader
	 */
	public function registerAutoloading()
	{
		// If we are setting an attribute that says we're loading
		// up using composer only, we'll stop now
		if ($this->autoload === 'composer')
		{
			return;
		}
		elseif ($this->autoload instanceof Closure)
		{
			$loader = new ClassLoader;

			// Put this first incase the closure overrides
			$loader->setUseIncludePath(true);

			call_user_func_array($this->autoload, array($loader, $this));
			$loader->register();
			return $this->autoloaders[] = $loader;
		}
		else
		{
			return $this->registerDefaultAutoloading();
		}
	}

	/**
	 * Registers default autoloading for the Extension.
	 *
	 * @return Composer\Autoload\ClassLoader
	 */
	public function registerDefaultAutoloading()
	{
		$loader = new ClassLoader;

		$loader->add($this->namespace, $this->path.'/src');

		$loader->register();
		$loader->setUseIncludePath(true);
		return $this->autoloaders[] = $loader;
	}

	/**
	 * Setup extension routes.
	 *
	 * @return void
	 */
	public function setupRoutes()
	{
		if ( ! $this->isBooted())
		{
			throw new \RuntimeException("Cannot register routes for Extension [{$this->slug}] as it is not booted.");
		}

		if (isset($this->routes) and $this->routes instanceof Closure)
		{
			$container = $this->getContainer();

			call_user_func_array($this->routes, array($this, $container));
		}
	}

	/**
	 * Registers a package with the container associated
	 * with the extension bag.
	 *
	 * @return void
	 */
	public function registerPackage()
	{
		// If there is no IoC container associated with our
		// bag, let's just ditch out now.
		if ( ! $container = $this->getContainer()) return;

		// The package is our slug. If your extension is
		// foo/bar, then the package registered is foo/bar
		$package   = $this->slug;

		// Our namespace also matches our package. Traditionally,
		// Laravel only takes the {package} part from {vendor}/{package}
		// but this means we cannot have multiple vendors using the
		// same package. If your extension is foo/bar, you'll
		// access config as 'foo/bar::file.key', not 'bar::file.key'
		$namespace = $package;

		$config = $this->path.'/config';

		if ($container['files']->isDirectory($config))
		{
			$container['config']->package($package, $config, $namespace);
		}

		// Next we will check for any "language" components. If language files exist
		// we will register them with this given package's namespace so that they
		// may be accessed using the translation facilities of the application.
		$lang = $this->path.'/lang';

		if ($container['files']->isDirectory($lang))
		{
			$container['translator']->addNamespace($namespace, $lang);
		}

		// Finally we will register the view namespace so that we can access each of
		// the views available in this package. We use a standard convention when
		// registering the paths to every package's views and other components.
		$view = $this->path.'/views';

		if ($container['files']->isDirectory($view))
		{
			$container['view']->addNamespace($namespace, $view);
		}
	}

	/**
	 * Returns the container associated with the
	 * extension's bag.
	 *
	 * @return Illuminate\Container\Container
	 */
	public function getContainer()
	{
		return $this->extensionBag->getContainer();
	}

	/**
	 * Inserts data in the database and marges the
	 * attributes inserted with our current database
	 * attributes.
	 *
	 * @param  array $attributes
	 * @return void
	 */
	public function databaseInsert(array $attributes)
	{
		$this->getConnection()->table('extensions')->insert($attributes);
		$this->databaseAttributes = array_merge($this->databaseAttributes, $attributes);
	}

	/**
	 * Updates data in the database and marges the
	 * attributes updated with our current database
	 * attributes.
	 *
	 * @param  array $attributes
	 * @return void
	 */
	public function databaseUpdate(array $attributes)
	{
		$this->getConnection()->table('extensions')->where('slug', '=', $this->slug)->update($attributes);

		$this->databaseAttributes = array_merge($this->databaseAttributes, $attributes);
	}

	/**
	 * Deletes data in the database and empties
	 * the database attribues
	 *
	 * @param  array $attributes
	 * @return void
	 */
	public function databaseDelete()
	{
		$this->getConnection()->table('extensions')->where('slug', '=', $this->slug)->delete();
		$this->databaseAttributes = array();
	}

	/**
	 * Hydrates the database attributes.
	 *
	 * @return void
	 */
	public function hydrateDatabaseAttributes()
	{
		$databaseAttributes = (array) $this->getConnection()->table('extensions')->where('slug', '=', $this->slug)->first();

		if (isset($databaseAttributes['enabled']))
		{
			$databaseAttributes['enabled'] = (bool) $databaseAttributes['enabled'];
		}

		$this->databaseAttributes = $databaseAttributes;
	}

	/**
	 * Get all of the current attributes for the theme.
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Set the Theme's attributes.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function setAttributes(array $attributes)
	{
		$this->attributes = $attributes;
	}

	/**
	 * Fill the theme with an array of attributes.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function fill(array $attributes)
	{
		foreach ($attributes as $key => $value)
		{
			$this->setAttribute($key, $value);
		}
	}

	/**
	 * Set a given attribute on the Theme.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function setAttribute($key, $value)
	{
		$this->attributes[$key] = $value;
	}

	/**
	 * Get an attribute from the theme.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function getAttribute($key, $default = null)
	{
		if (array_key_exists($key, $this->attributes))
		{
			return $this->attributes[$key];
		}

		return value($default);
	}

	/**
	 * Get all of the current database attributes on the extension.
	 *
	 * @return array
	 */
	public function getDatabaseAttributes()
	{
		return $this->databaseAttributes;
	}

	/**
	 * Set the array of extension database attributes. No checking is done.
	 *
	 * @param  array  $databaseAttributes
	 * @return void
	 */
	public function setDatabaseAttributes(array $databaseAttributes)
	{
		$this->databaseAttributes = $databaseAttributes;
	}

	/**
	 * Get the database connection for the model.
	 *
	 * @return Illuminate\Database\Connection
	 */
	public function getConnection()
	{
		return static::resolveConnection($this->connection);
	}

	/**
	 * Get the current connection name for the model.
	 *
	 * @return string
	 */
	public function getConnectionName()
	{
		return $this->connection;
	}

	/**
	 * Set the connection associated with the model.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setConnection($name)
	{
		$this->connection = $name;
	}

	/**
	 * Register a "registering" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function registering(Closure $callback)
	{
		static::registerEvent('registering', $callback);
	}

	/**
	 * Register a "registered" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function registered(Closure $callback)
	{
		static::registerEvent('registered', $callback);
	}

	/**
	 * Register a "booting" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function booting(Closure $callback)
	{
		static::registerEvent('booting', $callback);
	}

	/**
	 * Register a "booted" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function booted(Closure $callback)
	{
		static::registerEvent('booted', $callback);
	}

	/**
	 * Register a "installing" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function installing(Closure $callback)
	{
		static::registerEvent('installing', $callback);
	}

	/**
	 * Register a "installed" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function installed(Closure $callback)
	{
		static::registerEvent('installed', $callback);
	}

	/**
	 * Register a "uninstalling" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function uninstalling(Closure $callback)
	{
		static::registerEvent('uninstalling', $callback);
	}

	/**
	 * Register a "uninstalled" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function uninstalled(Closure $callback)
	{
		static::registerEvent('uninstalled', $callback);
	}

	/**
	 * Register a "enabling" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function enabling(Closure $callback)
	{
		static::registerEvent('enabling', $callback);
	}

	/**
	 * Register a "enabled" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function enabled(Closure $callback)
	{
		static::registerEvent('enabled', $callback);
	}

	/**
	 * Register a "disabling" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function disabling(Closure $callback)
	{
		static::registerEvent('disabling', $callback);
	}

	/**
	 * Register a "disabled" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function disabled(Closure $callback)
	{
		static::registerEvent('disabled', $callback);
	}

	/**
	 * Register a "upgrading" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function upgrading(Closure $callback)
	{
		static::registerEvent('upgrading', $callback);
	}

	/**
	 * Register a "upgraded" callback.
	 *
	 * @param  Closure  $callback
	 * @return void
	 */
	public static function upgraded(Closure $callback)
	{
		static::registerEvent('upgraded', $callback);
	}

	/**
	 * Listen for an event on the extension.
	 *
	 * @param  string   $name
	 * @param  Closure  $callback
	 * @return void
	 */
	protected static function registerEvent($name, Closure $callback)
	{
		if ( ! isset(static::$dispatcher)) return;

		static::$dispatcher->listen("extension.{$name}", $callback);
	}

	/**
	 * Fires an event for the extension.
	 *
	 * @param  string  $name
	 * @return mixed
	 */
	protected function fireEvent($name)
	{
		if ( ! isset(static::$dispatcher)) return;

		return static::$dispatcher->fire("extension.{$name}", array($this));
	}

	/**
	 * Migrate the extension with an optional customized
	 * path.
	 *
	 * @param  string  $path
	 * @return void
	 */
	protected function migrate($path = null)
	{
		if ( ! isset(static::$migrator)) return;

		$path = $path ?: $this->getMigrationsPath();

		static::$migrator->run($path);
	}

	/**
	 * Reset the migrations for the extnesion with an
	 * optional customized path.
	 *
	 * @param  string  $path
	 * @return void
	 */
	protected function resetMigrations($path = null)
	{
		if ( ! isset(static::$migrator)) return;

		$path = $path ?: $this->getMigrationsPath();

		$files = static::$migrator->getMigrationFiles($path);
		$repository = static::$migrator->getRepository();

		// Get an array of migration names which will be
		// reset
		$migrations = array_intersect(array_reverse($repository->getRan()), $files);

		// Loop through the migrations we have to rollback
		foreach ($migrations as $migration)
		{
			// Let the migrator resolve the migration instance
			$instance = static::$migrator->resolve($migration);

			// And we'll call the down method on the migration
			$instance->down();

			// Now we need to manipulate what the migrator does to
			// delete a migration
			$migrationClass = new \StdClass;
			$migrationClass->migration = $migration;
			$repository->delete($migrationClass);
		}
	}

	/**
	 * Resolve a connection instance by name.
	 *
	 * @param  string  $connection
	 * @return Illuminate\Database\Connection
	 */
	public static function resolveConnection($connection)
	{
		return static::$resolver->connection($connection);
	}

	/**
	 * Get the connection resolver instance.
	 *
	 * @return Illuminate\Database\ConnectionResolverInterface
	 */
	public static function getConnectionResolver()
	{
		return static::$resolver;
	}

	/**
	 * Set the connection resolver instance.
	 *
	 * @param  Illuminate\Database\ConnectionResolverInterface  $resolver
	 * @return void
	 */
	public static function setConnectionResolver(Resolver $resolver)
	{
		static::$resolver = $resolver;
	}

	/**
	 * Get the event dispatcher instance.
	 *
	 * @return Illuminate\Events\Dispatcher
	 */
	public static function getEventDispatcher()
	{
		return static::$dispathcer;
	}

	/**
	 * Set the event dispatcher instance.
	 *
	 * @param  Illuminate\Events\Dispatcher
	 * @return void
	 */
	public static function setEventDispatcher(Dispatcher $dispatcher)
	{
		static::$dispatcher = $dispatcher;
	}

	/**
	 * Unset the event dispatcher for models.
	 *
	 * @return void
	 */
	public static function unsetEventDispatcher()
	{
		static::$dispatcher = null;
	}

	/**
	 * Get the database migrator instance.
	 *
	 * @return Illuminate\Database\Migrations\Migrator
	 */
	public static function getMigrator()
	{
		return static::$dispathcer;
	}

	/**
	 * Set the database migrator instance.
	 *
	 * @param  Illuminate\Database\Migrations\Migrator
	 * @return void
	 */
	public static function setMigrator(Migrator $migrator)
	{
		static::$migrator = $migrator;
	}

	/**
	 * Unset the database migrator for models.
	 *
	 * @return void
	 */
	public static function unsetMigrator()
	{
		static::$migrator = null;
	}

	/**
	 * Dynamically retrieve attributes on the object.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->getAttribute($key);
	}

	/**
	 * Dynamically set attributes on the object.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->setAttribute($key, $value);
	}

	/**
	 * Determine if an attribute exists on the object.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __isset($key)
	{
		return isset($this->attributes[$key]);
	}

	/**
	 * Unset an attribute on the object.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __unset($key)
	{
		unset($this->attributes[$key]);
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		$array['attributes'] = $this->attributes;

		foreach ($array['attributes'] as $key => $value)
		{
			if ($value instanceof Closure)
			{
				unset($array['attributes'][$key]);
			}
		}

		$properties = array('registered', 'booted', 'slug', 'path', 'namespace', 'databaseAttributes');

		foreach ($properties as $property)
		{
			$array[$property] = $this->$property;
		}

		return $array;
	}

	/**
	 * Convert the object to its JSON representation.
	 *
	 * @param  int  $options
	 * @return string
	 */
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}

}
