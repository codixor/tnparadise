<?php namespace Cartalyst\SentrySocial\Links;
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

use Cartalyst\Sentry\Users\UserInterface;
use Illuminate\Database\Eloquent\Model;
use League\OAuth1\Client\Credentials\TokenCredentials as OAuth1TokenCredentials;
use League\OAuth2\Client\Token\AccessToken as OAuth2AccessToken;

class EloquentLink extends Model implements LinkInterface {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'social';

	/**
	 * {@inheritDoc}
	 */
	protected $fillable = array(
		'provider',
		'uid',
		'oauth1_token_identifier',
		'oauth1_token_secret',
		'oauth2_access_token',
		'oauth2_refresh_token',
		'oauth2_expires',
	);

	/**
	 * The users model name.
	 *
	 * @var string
	 */
	protected static $usersModel = 'Cartalyst\Sentry\Users\EloquentUser';

	/**
	 * User relationship
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo(static::$usersModel, 'user_id');
	}

	/**
	 * Get the attributes that should be converted to dates.
	 *
	 * @return array
	 */
	public function getDates()
	{
		return array_merge(parent::getDates(), array('oauth2_expires'));
	}

	/**
	 * {@inheritDoc}
	 */
	public function storeToken($token)
	{
		if ($token instanceof OAuth1TokenCredentials)
		{
			$this->oauth1_token_identifier = $token->getIdentifier();
			$this->oauth1_token_secret     = $token->getSecret();
		}
		elseif ($token instanceof OAuth2AccessToken)
		{
			$this->oauth2_access_token  = $token->accessToken;
			$this->oauth2_refresh_token = $token->refreshToken;
			$this->oauth2_expires       = $token->expires;
		}
		else
		{
			throw new \InvalidArgumentException('Invalid token type ['.gettype($token).'] passed to be stored.');
		}

		$this->save();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUser(UserInterface $user)
	{
		$this->user()->associate($user);

		$this->save();
	}

	/**
	 * Get the users model.
	 *
	 * @return string
	 */
	public static function getUsersModel()
	{
		return static::$usersModel;
	}

	/**
	 * Set the users model.
	 *
	 * @param  string  $usersModel
	 * @return void
	 */
	public static function setUsersModel($usersModel)
	{
		static::$usersModel = $usersModel;
	}

}
