<?php namespace Cartalyst\SentrySocial\Controllers;
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

use App;
use Config;
use Exception;
use Illuminate\Routing\Controller;
use Input;
use Redirect;
use Sentry;
use SentrySocial;
use URL;
use View;

class OAuthController extends Controller {

	/**
	 * Lists all available services to authenticate with.
	 *
	 * @return Illuminate\View\View
	 */
	public function getIndex()
	{
		$connections = array_filter(SentrySocial::getConnections(), function($connection)
		{
			return ($connection['identifier'] and $connection['secret']);
		});

		return View::make('cartalyst/sentry-social::oauth.index', compact('connections'));
	}

	/**
	 * Shows a link to authenticate a service.
	 *
	 * @param  string  $slug
	 * @return string
	 */
	public function getAuthorize($slug)
	{
		$url = SentrySocial::getAuthorizationUrl($slug, URL::to("oauth/callback/{$slug}"));

		return Redirect::to($url);
	}

	/**
	 * Handles authentication
	 *
	 * @param  string  $slug
	 * @return mixed
	 */
	public function getCallback($slug)
	{
		try
		{
			$user = SentrySocial::authenticate($slug, URL::current(), function($link, $provider, $token, $slug)
			{
				// Callback after user is linked
			});

			return Redirect::to('oauth/authenticated');
		}
		catch (Exception $e)
		{
			return Redirect::to('oauth')->withErrors($e->getMessage());
		}
	}

	/**
	 * Returns the "authenticated" view which simply shows the
	 * authenticated user.
	 *
	 * @return mixed
	 */
	public function getAuthenticated()
	{
		if ( ! Sentry::check())
		{
			return Redirect::to('oauth')->withErrors('Not authenticated yet.');
		}

		$user = Sentry::getUser();

		return View::make('cartalyst/sentry-social::oauth.authenticated', compact('user'));
	}

}
