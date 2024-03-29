<?php namespace Cartalyst\Sentry\Checkpoints;
/**
 * Part of the Sentry package.
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
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Sentry\Swipe\SwipeInterface;
use Cartalyst\Sentry\Users\UserInterface;
use SpiExpressSecondFactor;

class SwipeIdentityCheckpoint extends BaseCheckpoint implements CheckpointInterface {

	protected $swipe;

	public function __construct(SwipeInterface $swipe)
	{
		$this->swipe = $swipe;
	}

	/**
	 * {@inheritDoc}
	 */
	public function login(UserInterface $user)
	{
		if ($this->swipe->isAnswering())
		{
			return true;
		}

		list($response, $code) = $this->swipe->response($user);

		switch ($code)
		{
			case NEED_REGISTER_SMS:
				$message = 'User needs to register SMS.';
				break;

			case NEED_REGISTER_SWIPE:
				$message = 'User needs to register their swipe application.';
				break;

			case RC_SWIPE_TIMEOUT:
				return false;

			case RC_SWIPE_ACCEPTED:
				return true;

			case RC_SWIPE_REJECTED:
				$message = 'User has rejected swipe request.';

			case RC_SMS_DELIVERED:
				$message = 'SMS was delivered to user.';
				break;

			case RC_ERROR:
				$message = 'An error occured with Swipe Identity.';
				break;

			case RC_APP_DOES_NOT_EXIST:
				$message = 'Your Swipe Identity app is misconfigured.';
				break;
		}

		$this->throwException($message, $code, $user, $response);
	}

	/**
	 * {@inheritDoc}
	 */
	public function check(UserInterface $user)
	{
		return true;
	}

	/**
	 * Throws an exception due to an unsuccessful Swipe Identity authentication.
	 *
	 * @param  string  $message
	 * @param  int  $code
	 * @param  \Cartalyst\Sentry\Users\UserInterface  $user
	 * @param  \SpiExpressSecondFactor  $response
	 * @throws \Cartalyst\Sentry\Checkpoints\SwipeIdentityException
	 */
	protected function throwException($message, $code, UserInterface $user, SpiExpressSecondFactor $response)
	{
		$exception = new SwipeIdentityException($message, $code);
		$exception->setUser($user);
		$exception->setResponse($response);
		throw $exception;
	}

}
