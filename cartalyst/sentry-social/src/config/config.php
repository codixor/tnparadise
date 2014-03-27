<?php
/**
 * Part of the Sentry Social package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Sentry
 * @version    3.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

return array(

	/*
	|--------------------------------------------------------------------------
	| Connections
	|--------------------------------------------------------------------------
	|
	| Connections are simple. Each key is a unique slug for the connection. Use
	| anything, just make it unique. This is how you reference it in Sentry
	| Social. Each slug requires a driver, which must match a valid inbuilt
	| driver or may match your own custom class name that inherits from a
	| valid base driver.
	|
	| Make sure each connection contains an "identifier" and a "secret". Thse
	| are also known as "key" and "secret", "app id" and "app secret"
	| depending on the service. We're using "identifier" and
	| "secret" for consistency.
	|
	| OAuth2 providers may contain an optional "scopes" array, which is a
	| list of scopes you're requesting from the user for that connection.
	|
	| You may use multiple connections with the same driver!
	|
	*/

	'connections' => array(

		'facebook' => array(
			'driver'     => 'Facebook',
			'identifier' => '',
			'secret'     => '',
			'scopes'     => array('email'),
		),

		'github' => array(
			'driver'     => 'GitHub',
			'identifier' => '',
			'secret'     => '',
			'scopes'     => array('user'),
		),

		'google' => array(
			'driver'     => 'Google',
			'identifier' => '',
			'secret'     => '',
			'scopes'     => array(
				'https://www.googleapis.com/auth/userinfo.profile',
				'https://www.googleapis.com/auth/userinfo.email',
			),
		),

		'microsoft' => array(
			'driver'     => 'Microsoft',
			'identifier' => '',
			'secret'     => '',
			'scopes'     => array('wl.basic', 'wl.emails'),
		),

		'twitter' => array(
			'driver'     => 'Twitter',
			'identifier' => '',
			'secret'     => '',
		),

		'tumblr' => array(
			'driver'     => 'Tumblr',
			'identifier' => '',
			'secret'     => '',
		),

		'vkontakte' => array(
			'driver'     => 'Vkontakte',
			'identifier' => '',
			'secret'     => '',
			'scopes'     => array(),
		),

	),

	/*
	|--------------------------------------------------------------------------
	| Coming Soon
	|--------------------------------------------------------------------------
	|
	| The below connections are coming very soon. We're in the process of
	| adding the requirements to the underlying OAuth packages,
	| league/oauth1-client and league/oauth2-client. The
	| requirements we're adding are UID, email and
	| scren name fetching. As soon as they're
	| added, these drivers will be
	| available.
	|
	*/

		// 'bitly' => array(
		// 	'driver'     => 'bitly',
		// 	'identifier' => '',
		// 	'secret'     => '',
		// 	'scopes'     => array(),
		// ),

		// 'fitbit' => array(
		// 	'driver'     => 'Fitbit',
		// 	'identifier' => '',
		// 	'secret'     => '',
		// ),

		// 'foursquare' => array(
		// 	'driver'     => 'Foursquare',
		// 	'identifier' => '',
		// 	'secret'     => '',
		// 	'scopes'     => array(),
		// ),

		// 'soundcloud' => array(
		// 	'driver'     => 'SoundCloud',
		// 	'identifier' => '',
		// 	'secret'     => '',
		// 	'scopes'     => array(),
		// ),


		// 'yammer' => array(
		// 	'driver'     => 'Yammer',
		// 	'identifier' => '',
		// 	'secret'     => '',
		// 	'scopes'     => array(),
		// ),

		// 'linkedin' => array(
		// 	'driver'     => 'LinkedIn',
		// 	'identifier' => '',
		// 	'secret'     => '',
		// 	'scopes'     => array('r_fullprofile', 'r_emailaddress'),
		// ),

	/*
	|--------------------------------------------------------------------------
	| Social Link Model
	|--------------------------------------------------------------------------
	|
	| When users are registered, a "social link provider" will map the social
	| authentications with user instances. Feel free to use your own model
	| with our provider.
	|
	*/

	'link' => 'Cartalyst\SentrySocial\Links\EloquentLink',

);
