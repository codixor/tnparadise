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

use Cartalyst\Dependencies\DependentInterface;

interface ExtensionInterface extends DependentInterface {

	/**
	 * Returns the extension's path.
	 *
	 * @return string
	 */
	public function getPath();

	/**
	 * Returns the extension's namespace.
	 *
	 * @return string
	 */
	public function getNamespace();

	/**
	 * Returns if the extension is versioned
	 * or not.
	 *
	 * @return bool
	 */
	public function isVersioned();

	/**
	 * Returns the extension's version.
	 *
	 * @return string
	 */
	public function getVersion();

	/**
	 * Returns if an extension can be installed and a
	 * number of exceptions if it cannot.
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public function canInstall();

	/**
	 * Returns if an Extension is installed.
	 *
	 * @return bool
	 */
	public function isInstalled();

	/**
	 * Installs the Extension by running migrations
	 * and calling events which can be hooked into,
	 * for example used to update database attributes.
	 *
	 * @return void
	 */
	public function install();

	/**
	 * Returns if an extension can be uninstalled and a
	 * number of exceptions if it cannot.
	 *
	 * @return bool
	 */
	public function canUninstall();

	/**
	 * Returns if the Extension is uninstalled.
	 *
	 * @return bool
	 */
	public function isUninstalled();

	/**
	 * Uninstalls the Extension by running migrations
	 * and calling events which can be hooked into,
	 * for example used to update database attributes.
	 *
	 * @return void
	 */
	public function uninstall();

	/**
	 * Returns if an extension can be enabled and a
	 * number of exceptions if it cannot.
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public function canEnable();

	/**
	 * Returns whether an Extension is enabled or not.
	 *
	 * @return bool
	 */
	public function isEnabled();

	/**
	 * Enables the extension.
	 *
	 * @return void
	 */
	public function enable();

	/**
	 * Returns if the extension can be disabled.
	 *
	 * @return bool
	 */
	public function canDisable();

	/**
	 * Returns if the extension is disabled.
	 *
	 * @return bool
	 */
	public function isDisabled();

	/**
	 * Disables the extension.
	 *
	 * @return void
	 */
	public function disable();

	/**
	 * Returns whether an Extension needs upgrades or not.
	 *
	 * @return bool
	 */
	public function needsUpgrade();

	/**
	 * Upgrades the Extension by running migrations
	 * and calling events which can be hooked into,
	 * for example used to update database attributes.
	 *
	 * @return void
	 */
	public function upgrade();

	/**
	 * Returns if the extension is registered.
	 *
	 * @return bool
	 */
	public function isRegistered();

	/**
	 * Registers the extension. Called when added to the extension
	 * bag. All extensions should be registered before any are
	 * booted.
	 *
	 * @return void
	 */
	public function register();

	/**
	 * Returns if the extension is booted.
	 *
	 * @return bool
	 */
	public function isBooted();

	/**
	 * Boots the extension.
	 *
	 * @return void
	 */
	public function boot();

}
