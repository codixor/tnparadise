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

use Illuminate\Http\Request;

class IlluminateRequestProvider implements RequestProviderInterface {

	/**
	 * The request instance.
	 *
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	/**
	 * Creates a new Illuminate request provider.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOAuth1TemporaryCredentialsIdentifier()
	{
		return $this->request->input('oauth_token');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOAuth1Verifier()
	{
		return $this->request->input('oauth_verifier');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOAuth2Code()
	{
		return $this->request->input('code');
	}

}
