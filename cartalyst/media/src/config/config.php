<?php
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

return array(

	/*
	|--------------------------------------------------------------------------
	| Maximum allowed size for uploaded files
	|--------------------------------------------------------------------------
	|
	| Define here the maximum size of an uploaded file in bytes.
	|
	| Default value is 10 Mb.
	|
	*/

	'max_filesize' => 10485760,

	/*
	|--------------------------------------------------------------------------
	| Allowed types of files
	|--------------------------------------------------------------------------
	|
	| Specify here all the allowed mime types that can be uploaded.
	|
	| Look at http://www.iana.org/assignments/media-types for a
	| complete list of standard MIME types
	|
	*/

	'allowed_mimes' => array(

		// Audio & Video
		'audio/ogg', 'video/mp4', 'video/ogg',

		// Application
		'application/zip', 'application/pdf',

		// Images
		'image/gif', 'image/jpeg', 'image/png',

		// Text
		'text/plain',

	),

	/*
	|--------------------------------------------------------------------------
	| File dispersion
	|--------------------------------------------------------------------------
	|
	| This feature allows you to have a better and more organized file
	| structure that you dictate using placeholders.
	|
	| To disable this feature just set a "false" boolean as value.
	|
	| Reserved placeholders:
	|
	|	File information
	|		:name      -> foo
	|		:extension -> jpg
	|		:mime      -> image/jpeg
	|
	| Supported placeholders by default:
	|
	|	Current Year
	|		:yyyy  ->  2013
	|		:yy    ->  13
	|
	|	Current Month
	|		:mmmm  ->  November
	|		:mmm   ->  Nov
	|		:mm    ->  11
	|
	|	Current Day
	|		:dddd  ->  Friday
	|		:ddd   ->  Fri
	|		:dd    ->  24
	|
	| Note: You need to add the forward slash (/) at the end.
	|
	| Example:
	|
	|	'dispersion' => ':yyyy/:mm/'
	|
	|	All your media files will be stored, by default, on:
	|
	|		app/storage/media/2014/01/foo.jpg
	|		app/storage/media/2014/01/bar.png
	|		app/storage/media/2014/01/test.pdf
	|
	*/

	'dispersion' => ':yyyy/:mm/',

	/*
	|--------------------------------------------------------------------------
	| Placeholders
	|--------------------------------------------------------------------------
	|
	| Define here all the file dispersion placeholders.
	|
	*/

	'placeholders' => array(

		':yyyy' => date('Y'),
		':yy'   => date('y'),
		':mmmm' => date('F'),
		':mmm'  => date('M'),
		':mm'   => date('m'),
		':dddd' => date('l'),
		':ddd'  => date('D'),
		':dd'   => date('d'),

	),

	/*
	|--------------------------------------------------------------------------
	| Default Adapter
	|--------------------------------------------------------------------------
	|
	| Define here the adapter name that you want to use by default.
	|
	*/

	'default' => 'local',

	/*
	|--------------------------------------------------------------------------
	| Adapters
	|--------------------------------------------------------------------------
	|
	| Below are all the available adapter that you can use.
	|
	*/

	'adapters' => array(

		// Local
		'local' => array(

			'storage_path' => storage_path().'/media',

		),

		// Amazon S3
		'amazon' => array(

			'key'    => '[your key]',
			'secret' => '[your secret]',

		),

		// Dropbox
		'dropbox' => array(

			'token'    => null,
			'app_name' => null,

		),

		// File Transfer Protocol
		'ftp' => array(

			'host'     => 'ftp.example.com',
			'port'     => 21,
			'username' => 'username',
			'password' => 'password',
			'root'     => '/path/to/root',
			'passive'  => true,
			'ssl'      => true,
			'timeout'  => 30,

		),

		// SSH File Transfer Protocol
		'sftp' => array(

			'host'       => 'example.com',
			'port'       => 21,
			'username'   => 'username',
			'password'   => 'password',
			'privateKey' => 'path/to/or/contents/of/privatekey',
			'root'       => '/path/to/root',
			'timeout'    => 10,

		),

	),

);
