<?php namespace Cartalyst\SentrySocial\Tests;
/**
 * Part of the Data Grid package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Data Grid
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Mockery as m;
use Cartalyst\SentrySocial\RequestProviders\NativeProvider as Provider;
use PHPUnit_Framework_TestCase;

class NativeRequestProviderTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testOAuth1TemporaryCredentialsIdentifier()
	{
		$provider = new Provider;
		$_GET['oauth_token'] = 'oauth_token_value';
		$this->assertEquals('oauth_token_value', $provider->getOAuth1TemporaryCredentialsIdentifier());
	}

	public function testOAuth1Verifier()
	{
		$provider = new Provider;
		$_GET['oauth_verifier'] = 'verifier_value';
		$this->assertEquals('verifier_value', $provider->getOAuth1Verifier());
	}

	public function testOAuth2Code()
	{
		$provider = new Provider;
		$_GET['code'] = 'code_value';
		$this->assertEquals('code_value', $provider->getOAuth2Code());
	}

}
