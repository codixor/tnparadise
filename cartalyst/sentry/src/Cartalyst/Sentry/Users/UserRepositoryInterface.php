<?php namespace Cartalyst\Sentry\Users;
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

use Closure;

interface UserRepositoryInterface {

	/**
	 * Finds a user by the given primary key.
	 *
	 * @param  int  $id
	 * @return \Cartalyst\Sentry\Users\UserInterface
	 */
	public function findById($id);

	/**
	 * Finds a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Cartalyst\Sentry\Users\UserInterface
	 */
	public function findByCredentials(array $credentials);

	/**
	 * Finds a user by the given persistence code.
	 *
	 * @param  string  $code
	 * @return \Cartalyst\Sentry\Users\UserInterface
	 */
	public function findByPersistenceCode($code);

	/**
	 * Records a login for the given user.
	 *
	 * @param  \Cartalyst\Sentry\Users\UserInterface  $user
	 * @return \Cartalyst\Sentry\Users\UserInterface|bool
	 */
	public function recordLogin(UserInterface $user);

	/**
	 * Records a logout for the given user.
	 *
	 * @param  \Cartalyst\Sentry\Users\UserInterface  $user
	 * @return \Cartalyst\Sentry\Users\UserInterface|bool
	 */
	public function recordLogout(UserInterface $user);

	/**
	 * Validate the password of the given user.
	 *
	 * @param  \Cartalyst\Sentry\Users\UserInterface  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(UserInterface $user, array $credentials);

	/**
	 * Validate if the given user is valid for creation.
	 *
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validForCreation(array $credentials);

	/**
	 * Validate if the given user is valid for updating.
	 *
	 * @param  \Cartalyst\Sentry\Users\UserInterface|int  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validForUpdate($user, array $credentials);

	/**
	 * Creates a user.
	 *
	 * @param  array  $credentials
	 * @param  \Closure  $callback
	 * @return \Cartalyst\Sentry\Users\UserInterface
	 */
	public function create(array $credentials, Closure $callback = null);

	/**
	 * Updates a user.
	 *
	 * @param  \Cartalyst\Sentry\Users\UserInterface|int  $user
	 * @param  array  $credentials
	 * @return \Cartalyst\Sentry\Users\UserInterface
	 */
	public function update($user, array $credentials);

}
