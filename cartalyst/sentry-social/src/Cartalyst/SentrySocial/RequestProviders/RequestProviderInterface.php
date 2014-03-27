<?php namespace Cartalyst\SentrySocial\RequestProviders;
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

interface RequestProviderInterface {

	/**
	 * Get the OAuth 1 temporary credentials identifier from the query string.
	 *
	 * @return string
	 */
	public function getOAuth1TemporaryCredentialsIdentifier();

	/**
	 * Get the OAuth 1 verifier code from the query string.
	 *
	 * @return string
	 */
	public function getOAuth1Verifier();

	/**
	 * Get the OAuth 2 code from the query string used to retrieve access tokens.
	 *
	 * @return string
	 */
	public function getOAuth2Code();

}
