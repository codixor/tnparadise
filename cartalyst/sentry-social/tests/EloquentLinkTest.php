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
use Cartalyst\SentrySocial\Links\Eloquent\Link;
use League\OAuth1\Client\Credentials\TokenCredentials as OAuth1TokenCredentials;
use League\OAuth2\Client\Token\AccessToken as OAuth2AccessToken;
use PHPUnit_Framework_TestCase;

class EloquentLinkTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidTokenType()
	{
		$link = new Link;
		$token = new \stdClass;
		$link->storeToken($token);
	}

	public function testStoringOAuth1Token()
	{
		$link = m::mock('Cartalyst\SentrySocial\Links\Eloquent\Link[save]');
		$tokenCredentials = new OAuth1TokenCredentials;
		$tokenCredentials->setIdentifier('foo');
		$tokenCredentials->setSecret('bar');

		$link->shouldReceive('save')->once();

		$link->storeToken($tokenCredentials);
		$this->assertEquals('foo', $link->oauth1_token_identifier);
		$this->assertEquals('bar', $link->oauth1_token_secret);
	}

	public function testStoringOAuth2Token()
	{
		$link = m::mock('Cartalyst\SentrySocial\Links\Eloquent\Link[save]');
		$this->addMockConnection($link);
		$link->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
		$accessToken = new OAuth2AccessToken(array(
			'access_token' => 'foo',
			'expires_in' => 10,
			'refresh_token' => 'bar',
		));

		$link->shouldReceive('save')->once();

		$link->storeToken($accessToken);
		$this->assertEquals('foo', $link->oauth2_access_token);
		$this->assertEquals('bar', $link->oauth2_refresh_token);

		// Compare timestamp from date
		$this->assertInstanceOf('DateTime', $link->oauth2_expires);
		$this->assertEquals(time() + 10, $link->oauth2_expires->getTimestamp());
	}

	protected function addMockConnection($model)
	{
		$model->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
		$resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
		$model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
		$model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock('Illuminate\Database\Query\Processors\Processor'));
	}

}
