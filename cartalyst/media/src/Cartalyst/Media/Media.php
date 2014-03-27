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

use Cartalyst\Media\Exceptions\InvalidFileException;
use Cartalyst\Media\Exceptions\InvalidMimeTypeException;
use Cartalyst\Media\Exceptions\MaxFileSizeExceededException;
use Illuminate\Events\Dispatcher;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media {

	/**
	 * The filesystem instance.
	 *
	 * @var \League\Flysystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * The event dispatcher instance.
	 *
	 * @var \Illuminate\Events\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Holds the max file size limit
	 *
	 * @var int
	 */
	protected $maxFileSize = 10485760;

	/**
	 * Holds all the allowed mime types.
	 *
	 * @var array
	 */
	protected $allowedMimes = array();

	/**
	 * Holds all the available placeholders.
	 *
	 * @var array
	 */
	protected $placeholders = array();

	/**
	 * Constructor.
	 *
	 * @param  \League\Flysystem\Filesystem  $filesystem
	 * @param  \Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function __construct(Filesystem $filesystem, Dispatcher $events)
	{
		$this->filesystem = $filesystem;

		$this->dispatcher = $events;
	}

	/**
	 * Returns the max file size limit.
	 *
	 * @return int
	 */
	public function getMaxFileSize()
	{
		return $this->maxFileSize;
	}

	/**
	 * Set the max file size limit.
	 *
	 * @param  int $size
	 * @return \Cartalyst\Media\Media
	 */
	public function setMaxFileSize($size)
	{
		$this->maxFileSize = $size;

		return $this;
	}

	/**
	 * Returns the allowed mime types.
	 *
	 * @return array
	 */
	public function getAllowedMimes()
	{
		return $this->allowedMimes;
	}

	/**
	 * Set the allowed mime types.
	 *
	 * @param  array  $mimes
	 * @return \Cartalyst\Media\Media
	 */
	public function setAllowedMimes($mimes)
	{
		$this->allowedMimes = $mimes;

		return $this;
	}

	/**
	 * Returns the placeholders.
	 *
	 * @return array
	 */
	public function getPlaceholders()
	{
		return $this->placeholders;
	}

	/**
	 * Set the placeholders.
	 *
	 * @param  array  $placeholders
	 * @return \Cartalyst\Media\Media
	 */
	public function setPlaceholders($placeholders)
	{
		$this->placeholders = $placeholders;

		return $this;
	}

	/**
	 * Returns the filesystem.
	 *
	 * @return \League\Flysystem\Filesystem
	 */
	public function getFilesystem()
	{
		return $this->filesystem;
	}

	/**
	 * Set the filesystem at runtime.
	 *
	 * @param  \League\Flysystem\Filesystem  $filesystem
	 * @return \Cartalyst\Media\Media
	 */
	public function setFilesystem(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;

		return $this;
	}

	/**
	 * Returns the event dispatcher.
	 *
	 * @return \Illuminate\Events\Dispatcher
	 */
	public function getDispatcher()
	{
		return $this->dispatcher;
	}

	/**
	 * Set the events dispatcher at runtime.
	 *
	 * @param  \Illuminate\Events\Dispatcher  $events
	 * @return \Cartalyst\Media\Media
	 */
	public function setDispatcher(Dispatcher $events)
	{
		$this->dispatcher = $events;

		return $this;
	}

	/**
	 * Returns the file dispersion.
	 *
	 * @return string
	 */
	public function getDispersion()
	{
		return $this->dispersion;
	}

	/**
	 * Set the file dispersion.
	 *
	 * @param  string  $dispersion
	 * @return \Cartalyst\Media\Media
	 */
	public function setDispersion($dispersion)
	{
		$this->dispersion = $dispersion;

		return $this;
	}

	/**
	 * Returns information about the given file.
	 *
	 * @param  string  $file
	 * @return \Cartalyst\Media\File
	 */
	public function getFile($file)
	{
		return $this->filesystem->get($file, new File($this->filesystem, $file));
	}

	/**
	 * Check if the given file is valid.
	 *
	 * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
	 * @return bool
	 * @throws \Cartalyst\Media\Exceptions\InvalidFileException
	 * @throws \Cartalyst\Media\Exceptions\MaxFileSizeExceededException
	 * @throws \Cartalyst\Media\Exceptions\InvalidMimeTypeException
	 */
	public function validateFile($file)
	{
		if ( ! $file instanceof UploadedFile)
		{
			throw new InvalidFileException;
		}

		$fileSize = $file->getSize();

		$fileMime = $file->getMimeType();

		$maxFileSize = $this->getMaxFileSize();

		$allowedMimes = $this->getAllowedMimes();

		// Validate the file size
		if ($fileSize > $maxFileSize)
		{
			throw new MaxFileSizeExceededException;
		}

		// Validate the file mime type
		if ( ! empty($allowedMimes) and ! in_array($fileMime, $allowedMimes))
		{
			throw new InvalidMimeTypeException;
		}

		return true;
	}

	/**
	 * Upload the file to the given destination.
	 *
	 * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $original
	 * @param  string  $destination
	 * @return \League\Flysystem\File
	 */
	public function upload($original, $destination = null)
	{
		$destination = $destination ?: $original->getClientOriginalName();

		$destination = $this->prepareFileLocation($original, $destination);

		$this->filesystem->write($destination, file_get_contents($original->getPathName()));

		$file = $this->getFile($destination);

		$this->dispatcher->fire('cartalyst.media.uploaded', array($file, $original));

		return $file;
	}

	/**
	 * Delete the given media file.
	 *
	 * @param  string  $file
	 * @return bool
	 */
	public function delete($file)
	{
		$this->filesystem->delete($file);

		$this->dispatcher->fire('cartalyst.media.deleted', $file);

		return true;
	}

	/**
	 * Prepares the file location name using the file dispersion feature.
	 *
	 * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
	 * @param  string  $destination
	 * @return string
	 */
	protected function prepareFileLocation($file, $destination)
	{
		$placeholders = array_merge($this->getPlaceholders(), array(
			':name'      => $file->getClientOriginalName(),
			':extension' => $file->getExtension(),
			':mime'      => $file->getMimeType(),
			' '          => '_',
		));

		$destination = $this->getDispersion().$destination;

		return str_replace(array_keys($placeholders), array_values($placeholders), $destination);
	}

}
