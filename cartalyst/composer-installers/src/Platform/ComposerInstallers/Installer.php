<?php namespace Platform\ComposerInstallers;
/**
 * Part of the Composer Installers package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Composer Installers
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class Installer extends LibraryInstaller {

	/**
	 * Package types to installer class map
	 *
	 * @var array
	 */
	private $supportedTypes = array(
		'extension' => 'ExtensionInstaller',
		'theme'     => 'ThemeInstaller',
	);

	/**
	 * {@inheritDoc}
	 */
	public function getInstallPath(PackageInterface $package)
	{
		$type        = $package->getType();
		$packageType = substr($type, strpos($type, '-') + 1);

		if ( ! isset($this->supportedTypes[$packageType]))
		{
			throw new \InvalidArgumentException(
				'Sorry the package type of this package is not yet supported.'
			);
		}

		$class     = 'Platform\\ComposerInstallers\\'.$this->supportedTypes[$packageType];
		$installer = new $class($package, $this->composer);

		return $installer->getInstallPath();
	}

	/**
	 * {@inheritDoc}
	 */
	public function supports($packageType)
	{
		if (preg_match('/^(\w+)-(\w+)$/', $packageType, $matches))
		{
			return (($matches[1] === 'platform') and isset($this->supportedTypes[$matches[2]]));
		}

		return false;
	}

}
