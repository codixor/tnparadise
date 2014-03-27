<?php namespace Cartalyst\Media;
/**
 * Part of the Media package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Media
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use League\Flysystem\File as FileHandler;

class File extends FileHandler {

	/**
	 * Holds all the valid images MIME Types.
	 *
	 * @var array
	 */
	protected $imagesMimeTypes = array(
		'image/bmp',
		'image/gif',
		'image/jpeg',
		'image/png',
	);

	/**
	 * Return the file name without the extension.
	 *
	 * @return string
	 */
	public function getName()
	{
		return str_replace(".{$this->getExtension()}", '', $this->path);
	}

	/**
	 * Returns the file full path.
	 *
	 * @return string
	 */
	public function getFullpath()
	{
		return $this->filesystem->getAdapter()->root . $this->path;
	}

	/**
	 * Checks if the uploaded media is an image.
	 *
	 * @return bool
	 */
	public function isImage()
	{
		return in_array($this->getMimetype(), $this->imagesMimeTypes);
	}

	/**
	 * Return the image width and height.
	 *
	 * @return array
	 */
	public function getImageSize()
	{
		list($width, $height) = getimagesize($this->getFullpath());

		return compact('width', 'height');
	}

	/**
	 * Returns the file extension.
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return pathinfo($this->path, PATHINFO_EXTENSION);
	}

}

