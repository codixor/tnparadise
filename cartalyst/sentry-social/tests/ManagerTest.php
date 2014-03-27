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
use Cartalyst\SentrySocial\Manager;
use InvalidProvider;
use Illuminate\Events\Dispatcher;
use PHPUnit_Framework_TestCase;

class ManagerTest extends PHPUnit_Framework_TestCase {

	protected $manager;

	protected $sentry;

	protected $requestProvider;

	protected $session;

	protected $dispatcher;

	protected $linkProvider;

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass()
	{
		require_once __DIR__.'/stubs/InvalidProvider.php';
		require_once __DIR__.'/stubs/ValidOAuth1Provider.php';
		require_once __DIR__.'/stubs/ValidOAuth2Provider.php';
	}

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->manager = new Manager(
			$this->sentry          = m::mock('Cartalyst\Sentry\Sentry'),
			$this->linkProvider    = m::mock('Cartalyst\SentrySocial\Links\ProviderInterface'),
			$this->requestProvider = m::mock('Cartalyst\SentrySocial\RequestProviders\ProviderInterface'),
			$this->session         = m::mock('Cartalyst\Sentry\Sessions\SessionInterface'),
			$this->dispatcher      = new Dispatcher
		);
	}

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testAddingConnection()
	{
		$this->manager->addConnection('foo', array('bar' => 'baz'));
		$this->assertCount(1, $this->manager->getConnections());
		$this->assertEquals(array('bar' => 'baz'), $this->manager->getConnection('foo'));
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testGettingNonExistentConnection()
	{
		$this->manager->getConnection('foo');
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testMakeNonExistentConnection()
	{
		$this->manager->make('foo', 'http://example.com/callback');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Class matching driver is required
	 */
	public function testMakeConnectionWithMissingDriver()
	{
		$this->manager->addConnection('foo', array());
		$this->manager->make('foo', 'http://example.com/callback');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage App identifier and secret are required
	 */
	public function testMakeConnectionWithMissingIdentifier()
	{
		$this->manager->addConnection('foo', array(
			'driver' => 'Foo',
		));
		$this->manager->make('foo', 'http://example.com/callback');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage App identifier and secret are required
	 */
	public function testMakeConnectionWithMissingSecret()
	{
		$this->manager->addConnection('foo', array(
			'driver' => 'Foo',
			'identifier' => 'bar',
		));
		$this->manager->make('foo', 'http://example.com/callback');
	}

	public function testMakeBuiltInOAuth1Connection()
	{
		$this->manager->addConnection('twitter', array(
			'driver'     => 'Twitter',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('twitter', 'http://example.com/callback');
		$this->assertInstanceOf('League\OAuth1\Client\Server\Twitter', $provider);
		$this->assertEquals('appid', $provider->getClientCredentials()->getIdentifier());
		$this->assertEquals('appsecret', $provider->getClientCredentials()->getSecret());
	}

	public function testMakeBuiltInOAuth2Connection()
	{
		$this->manager->addConnection('facebook', array(
			'driver'     => 'Facebook',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('facebook', 'http://example.com/callback');
		$this->assertInstanceOf('League\OAuth2\Client\Provider\Facebook', $provider);
		$this->assertEquals('appid', $provider->clientId);
		$this->assertEquals('appsecret', $provider->clientSecret);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage does not inherit from a compatible OAuth provider class
	 */
	public function testMakingCustomInvalidConnection()
	{
		$this->manager->addConnection('foo', array(
			'driver'     => 'InvalidProvider',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('foo', 'http://example.com/callback');
	}

	public function testMakingValidOAuth1Provider()
	{
		$this->manager->addConnection('foo', array(
			'driver'     => 'ValidOAuth1Provider',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('foo', 'http://example.com/callback');
		$this->assertInstanceOf('ValidOAuth1Provider', $provider);
		$this->assertEquals('appid', $provider->getClientCredentials()->getIdentifier());
		$this->assertEquals('appsecret', $provider->getClientCredentials()->getSecret());
	}

	public function testMakingValidOAuth2Provider()
	{
		$this->manager->addConnection('foo', array(
			'driver'     => 'ValidOAuth2Provider',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('foo', 'http://example.com/callback');
		$this->assertInstanceOf('ValidOAuth2Provider', $provider);
		$this->assertEquals('appid', $provider->clientId);
		$this->assertEquals('appsecret', $provider->clientSecret);
	}

	public function testGettingOAuth1AuthorizationUrl()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make,oauthVersion]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		$provider->shouldReceive('getTemporaryCredentials')->once()->andReturn('credentials');
		$this->session->shouldReceive('put')->with('credentials')->once();

		$provider->shouldReceive('getAuthorizationUrl')->once()->andReturn('uri');
		$this->assertEquals('uri', $manager->getAuthorizationUrl('foo', 'http://example.com/callback'));
	}

	public function testGettingOAuth2AuthorizationUrl()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make,oauthVersion]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\IdentityProvider'));

		$provider->shouldReceive('getAuthorizationUrl')->once()->andReturn('uri');
		$this->assertEquals('uri', $manager->getAuthorizationUrl('foo', 'http://example.com/callback'));
	}

	/**
	 * @expectedException Cartalyst\SentrySocial\AccessMissingException
	 * @expectedExceptionMessage Missing [oauth_token] parameter
	 */
	public function testAuthenticatingOAuth1WithMissingTemporaryIdentifier()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn(null);

		$user = $manager->authenticate('foo', 'http://example.com/callback');
	}

	/**
	 * @expectedException Cartalyst\SentrySocial\AccessMissingException
	 * @expectedExceptionMessage Missing [verifier] parameter
	 */
	public function testAuthenticatingOAuth1WithMissingVerifier()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('1az');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn(null);

		$user = $manager->authenticate('foo', 'http://example.com/callback');
	}

	public function testAuthenticatingOAuth1WithLinkedUser()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('identifier');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn('verifier');

		// Mock retrieving credentials from the underlying package
		$this->session->shouldReceive('get')->andReturn($temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials'));
		$provider->shouldReceive('getTokenCredentials')->with($temporaryCredentials, 'identifier', 'verifier')->once()->andReturn($tokenCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentrySocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($tokenCredentials)->once();

		// Logged in user
		$this->sentry->shouldReceive('getUser')->once()->andReturn(null);

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));
		$user->shouldReceive('getId')->once()->andReturn(123);

		// Sentry's jobs
		$this->sentry->shouldReceive('getThrottleProvider')->once()->andReturn($throttleProvider = m::mock('Cartalyst\Sentry\Throtting\ThrottleProvider'));
		$this->sentry->shouldReceive('getIpAddress')->once()->andReturn('127.0.0.1');

		// Checking throttle status
		$throttleProvider->shouldReceive('isEnabled')->once()->andReturn(true);
		$throttleProvider->shouldReceive('findByUserId')->with(123, '127.0.0.1')->once()->andReturn($throttle = m::mock('Cartalyst\Sentry\Throtting\ThrottleInterface'));
		$throttle->shouldReceive('check')->once();

		// And finally, logging a user in
		$this->sentry->shouldReceive('login')->with($user, true)->once();

		$me = $this;
		$manager->existing(function($link, $provider, $token, $slug, $name) use ($me)
		{
			// Check the name of the event
			$me->assertEquals('sentry.social.existing', $name);

			$_SERVER['__sentry_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentry_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentry_social_existing']));
		unset($_SERVER['__sentry_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentry_social_linking']));
		$eventArgs = $_SERVER['__sentry_social_linking'];
		unset($_SERVER['__sentry_social_linking']);

		$this->assertCount(5, $eventArgs);
		list($_link, $_provider, $_tokenCredentials, $_slug, $_name) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($tokenCredentials, $_tokenCredentials);
		$this->assertEquals('foo', $_slug);
		$this->assertEquals('sentry.social.linking', $_name);
	}

	public function testAuthenticatingOAuth1WithUnlinkedExistingUser()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('identifier');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn('verifier');

		// Mock retrieving credentials from the underlying package
		$this->session->shouldReceive('get')->andReturn($temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials'));
		$provider->shouldReceive('getTokenCredentials')->with($temporaryCredentials, 'identifier', 'verifier')->once()->andReturn($tokenCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentrySocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($tokenCredentials)->once();

		// Logged in user
		$this->sentry->shouldReceive('getUser')->once()->andReturn(null);

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->ordered()->once()->andReturn(null);

		// Retrieving an existing user
		$this->sentry->shouldReceive('getUserProvider')->once()->andReturn($userProvider = m::mock('Cartalyst\Sentry\Users\ProviderInterface'));
		$provider->shouldReceive('getUserEmail')->once()->andReturn('a@b.c');
		$userProvider->shouldReceive('findByLogin')->with('a@b.c')->once()->andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));
		$user->shouldReceive('getId')->once()->andReturn(123);
		$link->shouldReceive('setUser')->with($user)->once();
		$link->shouldReceive('getUser')->ordered()->once()->andReturn($user);

		// Sentry's jobs
		$this->sentry->shouldReceive('getThrottleProvider')->once()->andReturn($throttleProvider = m::mock('Cartalyst\Sentry\Throtting\ThrottleProvider'));
		$this->sentry->shouldReceive('getIpAddress')->once()->andReturn('127.0.0.1');

		// Checking throttle status
		$throttleProvider->shouldReceive('isEnabled')->once()->andReturn(true);
		$throttleProvider->shouldReceive('findByUserId')->with(123, '127.0.0.1')->once()->andReturn($throttle = m::mock('Cartalyst\Sentry\Throtting\ThrottleInterface'));
		$throttle->shouldReceive('check')->once();

		// And finally, logging a user in
		$this->sentry->shouldReceive('login')->with($user, true)->once();

		$me = $this;
		$manager->existing(function($link, $provider, $token, $slug, $name) use ($me)
		{
			// Check the name of the event
			$me->assertEquals('sentry.social.existing', $name);

			$_SERVER['__sentry_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentry_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentry_social_existing']));
		unset($_SERVER['__sentry_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentry_social_linking']));
		$eventArgs = $_SERVER['__sentry_social_linking'];
		unset($_SERVER['__sentry_social_linking']);

		$this->assertCount(5, $eventArgs);
		list($_link, $_provider, $_tokenCredentials, $_slug, $_name) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($tokenCredentials, $_tokenCredentials);
		$this->assertEquals('foo', $_slug);
		$this->assertEquals('sentry.social.linking', $_name);
	}

	public function testAuthenticatingOAuth1WithUnlinkedNonExistentUser()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('identifier');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn('verifier');

		// Mock retrieving credentials from the underlying package
		$this->session->shouldReceive('get')->andReturn($temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials'));
		$provider->shouldReceive('getTokenCredentials')->with($temporaryCredentials, 'identifier', 'verifier')->once()->andReturn($tokenCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentrySocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($tokenCredentials)->once();

		// Logged in user
		$this->sentry->shouldReceive('getUser')->once()->andReturn(null);

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->ordered()->once()->andReturn(null);

		// Retrieving an existing user
		$this->sentry->shouldReceive('getUserProvider')->once()->andReturn($userProvider = m::mock('Cartalyst\Sentry\Users\ProviderInterface'));
		$provider->shouldReceive('getUserEmail')->once()->andReturn('a@b.c');
		$userProvider->shouldReceive('findByLogin')->with('a@b.c')->once()->andThrow(new \Cartalyst\Sentry\Users\UserNotFoundException);

		// Determining user attributes
		$userProvider->shouldReceive('getEmptyUser')->once()->andReturn($emptyUser = m::mock('Cartalyst\Sentry\Users\UserInterface'));
		$emptyUser->shouldReceive('getLoginName')->once()->andReturn('login');
		$emptyUser->shouldReceive('getPasswordName')->once()->andReturn('password');
		$provider->shouldReceive('getUserScreenName')->once()->andReturn(array('Ben', 'Corlett'));

		// Create a user
		$me = $this;
		$userProvider->shouldReceive('create')->with(m::on(function($attributes) use ($me)
		{
			foreach (array('login', 'password', 'first_name', 'last_name') as $key)
			{
				$me->assertTrue(isset($attributes[$key]));
			}

			$me->assertEquals('a@b.c', $attributes['login']);
			$me->assertEquals('Ben', $attributes['first_name']);
			$me->assertEquals('Corlett', $attributes['last_name']);

			// Keep mockery happy
			return true;
		}))->once()->andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));

		// Activate the user
		$user->shouldReceive('getActivationCode')->once()->andReturn('activation_code');
		$user->shouldReceive('attemptActivation')->with('activation_code')->once();

		// And back on track
		$user->shouldReceive('getId')->once()->andReturn(123);
		$link->shouldReceive('setUser')->with($user)->once();
		$link->shouldReceive('getUser')->ordered()->once()->andReturn($user);

		// Sentry's jobs
		$this->sentry->shouldReceive('getThrottleProvider')->once()->andReturn($throttleProvider = m::mock('Cartalyst\Sentry\Throtting\ThrottleProvider'));
		$this->sentry->shouldReceive('getIpAddress')->once()->andReturn('127.0.0.1');

		// Checking throttle status
		$throttleProvider->shouldReceive('isEnabled')->once()->andReturn(true);
		$throttleProvider->shouldReceive('findByUserId')->with(123, '127.0.0.1')->once()->andReturn($throttle = m::mock('Cartalyst\Sentry\Throtting\ThrottleInterface'));
		$throttle->shouldReceive('check')->once();

		// And finally, logging a user in
		$this->sentry->shouldReceive('login')->with($user, true)->once();

		$me = $this;
		$manager->registering(function($link, $provider, $token, $slug, $name) use ($me)
		{
			// Check the name of the event
			$me->assertEquals('sentry.social.registering', $name);

			$_SERVER['__sentry_social_registering'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentry_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentry_social_registering']));
		unset($_SERVER['__sentry_social_registering']);

		$this->assertTrue(isset($_SERVER['__sentry_social_linking']));
		$eventArgs = $_SERVER['__sentry_social_linking'];
		unset($_SERVER['__sentry_social_linking']);

		$this->assertCount(5, $eventArgs);
		list($_link, $_provider, $_tokenCredentials, $_slug, $_name) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($tokenCredentials, $_tokenCredentials);
		$this->assertEquals('foo', $_slug);
		$this->assertEquals('sentry.social.linking', $_name);
	}

	public function testAuthenticatingOAuth1LoggedInUser()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('identifier');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn('verifier');

		// Mock retrieving credentials from the underlying package
		$this->session->shouldReceive('get')->andReturn($temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials'));
		$provider->shouldReceive('getTokenCredentials')->with($temporaryCredentials, 'identifier', 'verifier')->once()->andReturn($tokenCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentrySocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($tokenCredentials)->once();

		// Logged in user
		$this->sentry->shouldReceive('getUser')->once()->andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));
		$link->shouldReceive('setUser')->with($user)->once();

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->andReturn($user);

		$me = $this;
		$manager->existing(function($link, $provider, $token, $slug, $name) use ($me)
		{
			// Check the name of the event
			$me->assertEquals('sentry.social.existing', $name);

			$_SERVER['__sentry_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentry_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentry_social_existing']));
		unset($_SERVER['__sentry_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentry_social_linking']));
		$eventArgs = $_SERVER['__sentry_social_linking'];
		unset($_SERVER['__sentry_social_linking']);

		$this->assertCount(5, $eventArgs);
		list($_link, $_provider, $_tokenCredentials, $_slug, $_name) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($tokenCredentials, $_tokenCredentials);
		$this->assertEquals('foo', $_slug);
		$this->assertEquals('sentry.social.linking', $_name);
	}

	/**
	 * @expectedException Cartalyst\SentrySocial\AccessMissingException
	 * @expectedExceptionMessage Missing [code] parameter
	 */
	public function testAuthenticatingOAuth2WithMissingCode()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\IdentityProvider'));

		$this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn(null);

		$user = $manager->authenticate('foo', 'http://example.com/callback');
	}

	public function testAuthenticatingOAuth2WithLinkedUser()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\IdentityProvider'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn('code');

		// Mock retrieving credentials from the underlying package
		$provider->shouldReceive('getAccessToken')->with('authorization_code', array('code' => 'code'))->once()->andReturn($accessToken = m::mock('League\OAuth2\Client\Token\AccessToken'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentrySocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($accessToken)->once();

		// Logged in user
		$this->sentry->shouldReceive('getUser')->once()->andReturn(null);

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));
		$user->shouldReceive('getId')->once()->andReturn(123);

		// Sentry's jobs
		$this->sentry->shouldReceive('getThrottleProvider')->once()->andReturn($throttleProvider = m::mock('Cartalyst\Sentry\Throtting\ThrottleProvider'));
		$this->sentry->shouldReceive('getIpAddress')->once()->andReturn('127.0.0.1');

		// Checking throttle status
		$throttleProvider->shouldReceive('isEnabled')->once()->andReturn(true);
		$throttleProvider->shouldReceive('findByUserId')->with(123, '127.0.0.1')->once()->andReturn($throttle = m::mock('Cartalyst\Sentry\Throtting\ThrottleInterface'));
		$throttle->shouldReceive('check')->once();

		// And finally, logging a user in
		$this->sentry->shouldReceive('login')->with($user, true)->once();

		$me = $this;
		$manager->existing(function($link, $provider, $token, $slug, $name) use ($me)
		{
			// Check the name of the event
			$me->assertEquals('sentry.social.existing', $name);

			$_SERVER['__sentry_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentry_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentry_social_existing']));
		unset($_SERVER['__sentry_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentry_social_linking']));
		$eventArgs = $_SERVER['__sentry_social_linking'];
		unset($_SERVER['__sentry_social_linking']);

		$this->assertCount(5, $eventArgs);
		list($_link, $_provider, $_accessToken, $_slug, $_name) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($accessToken, $_accessToken);
		$this->assertEquals('foo', $_slug);
		$this->assertEquals('sentry.social.linking', $_name);
	}

	public function testAuthenticatingOAuth2WithUnlinkedExistingUser()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\IdentityProvider'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn('code');

		// Mock retrieving credentials from the underlying package
		$provider->shouldReceive('getAccessToken')->with('authorization_code', array('code' => 'code'))->once()->andReturn($accessToken = m::mock('League\OAuth2\Client\Token\AccessToken'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentrySocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($accessToken)->once();

		// Logged in user
		$this->sentry->shouldReceive('getUser')->once()->andReturn(null);

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->ordered()->once()->andReturn(null);

		// Retrieving an existing user
		$this->sentry->shouldReceive('getUserProvider')->once()->andReturn($userProvider = m::mock('Cartalyst\Sentry\Users\ProviderInterface'));
		$provider->shouldReceive('getUserEmail')->once()->andReturn('a@b.c');
		$userProvider->shouldReceive('findByLogin')->with('a@b.c')->once()->andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));
		$user->shouldReceive('getId')->once()->andReturn(123);
		$link->shouldReceive('setUser')->with($user)->once();
		$link->shouldReceive('getUser')->ordered()->once()->andReturn($user);

		// Sentry's jobs
		$this->sentry->shouldReceive('getThrottleProvider')->once()->andReturn($throttleProvider = m::mock('Cartalyst\Sentry\Throtting\ThrottleProvider'));
		$this->sentry->shouldReceive('getIpAddress')->once()->andReturn('127.0.0.1');

		// Checking throttle status
		$throttleProvider->shouldReceive('isEnabled')->once()->andReturn(true);
		$throttleProvider->shouldReceive('findByUserId')->with(123, '127.0.0.1')->once()->andReturn($throttle = m::mock('Cartalyst\Sentry\Throtting\ThrottleInterface'));
		$throttle->shouldReceive('check')->once();

		// And finally, logging a user in
		$this->sentry->shouldReceive('login')->with($user, true)->once();

		$me = $this;
		$manager->existing(function($link, $provider, $token, $slug, $name) use ($me)
		{
			// Check the name of the event
			$me->assertEquals('sentry.social.existing', $name);

			$_SERVER['__sentry_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentry_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentry_social_existing']));
		unset($_SERVER['__sentry_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentry_social_linking']));
		$eventArgs = $_SERVER['__sentry_social_linking'];
		unset($_SERVER['__sentry_social_linking']);

		$this->assertCount(5, $eventArgs);
		list($_link, $_provider, $_accessToken, $_slug, $_name) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($accessToken, $_accessToken);
		$this->assertEquals('foo', $_slug);
		$this->assertEquals('sentry.social.linking', $_name);
	}

	public function testAuthenticatingOAuth2WithUnlinkedNonExistentUser()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
		$manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\IdentityProvider'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn('code');

		// Mock retrieving credentials from the underlying package
		$provider->shouldReceive('getAccessToken')->with('authorization_code', array('code' => 'code'))->once()->andReturn($accessToken = m::mock('League\OAuth2\Client\Token\AccessToken'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentrySocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($accessToken)->once();

		// Logged in user
		$this->sentry->shouldReceive('getUser')->once()->andReturn(null);

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->ordered()->once()->andReturn(null);

		// Retrieving an existing user
		$this->sentry->shouldReceive('getUserProvider')->once()->andReturn($userProvider = m::mock('Cartalyst\Sentry\Users\ProviderInterface'));
		$provider->shouldReceive('getUserEmail')->once()->andReturn('a@b.c');
		$userProvider->shouldReceive('findByLogin')->with('a@b.c')->once()->andThrow(new \Cartalyst\Sentry\Users\UserNotFoundException);

		// Determining user attributes
		$userProvider->shouldReceive('getEmptyUser')->once()->andReturn($emptyUser = m::mock('Cartalyst\Sentry\Users\UserInterface'));
		$emptyUser->shouldReceive('getLoginName')->once()->andReturn('login');
		$emptyUser->shouldReceive('getPasswordName')->once()->andReturn('password');
		$provider->shouldReceive('getUserScreenName')->once()->andReturn(array('Ben', 'Corlett'));

		// Create a user
		$me = $this;
		$userProvider->shouldReceive('create')->with(m::on(function($attributes) use ($me)
		{
			foreach (array('login', 'password', 'first_name', 'last_name') as $key)
			{
				$me->assertTrue(isset($attributes[$key]));
			}

			$me->assertEquals('a@b.c', $attributes['login']);
			$me->assertEquals('Ben', $attributes['first_name']);
			$me->assertEquals('Corlett', $attributes['last_name']);

			// Keep mockery happy
			return true;
		}))->once()->andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));

		// Activate the user
		$user->shouldReceive('getActivationCode')->once()->andReturn('activation_code');
		$user->shouldReceive('attemptActivation')->with('activation_code')->once();

		// And back on track
		$user->shouldReceive('getId')->once()->andReturn(123);
		$link->shouldReceive('setUser')->with($user)->once();
		$link->shouldReceive('getUser')->ordered()->once()->andReturn($user);

		// Sentry's jobs
		$this->sentry->shouldReceive('getThrottleProvider')->once()->andReturn($throttleProvider = m::mock('Cartalyst\Sentry\Throtting\ThrottleProvider'));
		$this->sentry->shouldReceive('getIpAddress')->once()->andReturn('127.0.0.1');

		// Checking throttle status
		$throttleProvider->shouldReceive('isEnabled')->once()->andReturn(true);
		$throttleProvider->shouldReceive('findByUserId')->with(123, '127.0.0.1')->once()->andReturn($throttle = m::mock('Cartalyst\Sentry\Throtting\ThrottleInterface'));
		$throttle->shouldReceive('check')->once();

		// And finally, logging a user in
		$this->sentry->shouldReceive('login')->with($user, true)->once();

		$me = $this;
		$manager->registering(function($link, $provider, $token, $slug, $name) use ($me)
		{
			// Check the name of the event
			$me->assertEquals('sentry.social.registering', $name);

			$_SERVER['__sentry_social_registering'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentry_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentry_social_registering']));
		unset($_SERVER['__sentry_social_registering']);

		$this->assertTrue(isset($_SERVER['__sentry_social_linking']));
		$eventArgs = $_SERVER['__sentry_social_linking'];
		unset($_SERVER['__sentry_social_linking']);

		$this->assertCount(5, $eventArgs);
		list($_link, $_provider, $_accessToken, $_slug, $_name) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($accessToken, $_accessToken);
		$this->assertEquals('foo', $_slug);
		$this->assertEquals('sentry.social.linking', $_name);
	}

	public function testAuthenticatingOAuth2LoggedInUser()
	{
		$manager = m::mock('Cartalyst\SentrySocial\Manager[make]');
        $manager->__construct($this->sentry, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

        $manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\IdentityProvider'));

        // Request proxy
        $this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn('code');

        // Mock retrieving credentials from the underlying package
        $provider->shouldReceive('getAccessToken')->with('authorization_code', array('code' => 'code'))->once()->andReturn($accessToken = m::mock('League\OAuth2\Client\Token\AccessToken'));

        // Unique ID
        $provider->shouldReceive('getUserUid')->once()->andReturn(789);

        // Finding an appropriate link
        $this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentrySocial\Links\LinkInterface'));
        $link->shouldReceive('storeToken')->with($accessToken)->once();

		// Logged in user
		$this->sentry->shouldReceive('getUser')->once()->andReturn($user = m::mock('Cartalyst\Sentry\Users\UserInterface'));
		$link->shouldReceive('setUser')->with($user)->once();

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->andReturn($user);

		$me = $this;
		$manager->existing(function($link, $provider, $token, $slug, $name) use ($me)
		{
			// Check the name of the event
			$me->assertEquals('sentry.social.existing', $name);

			$_SERVER['__sentry_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentry_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentry_social_existing']));
		unset($_SERVER['__sentry_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentry_social_linking']));
		$eventArgs = $_SERVER['__sentry_social_linking'];
		unset($_SERVER['__sentry_social_linking']);

		$this->assertCount(5, $eventArgs);
		list($_link, $_provider, $_accessToken, $_slug, $_name) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($accessToken, $_accessToken);
		$this->assertEquals('foo', $_slug);
		$this->assertEquals('sentry.social.linking', $_name);
	}

}
