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

use Cartalyst\SentrySocial\Services\ServiceInterface;
use League\OAuth1\Client\Server\Server as OAuth1Server;
use League\OAuth2\Client\Provider\IdentityProvider as OAuth2Provider;

class IlluminateLinkRepository implements LinkRepositoryInterface {

	/**
	 * The Eloquent social model.
	 *
	 * @var string
	 */
	protected $model = 'Cartalyst\SentrySocial\Links\EloquentLink';

	/**
	 * Create a new Eloquent Social Link provider.
	 *
	 * @param  string  $model
	 * @return void
	 */
	public function __construct($model = null)
	{
		if (isset($model))
		{
			$this->model = $model;
		}
	}

	/**
	 * Finds a link (or creates one) for the given provider slug and uid.
	 *
	 * @param  string  $slug
	 * @param  mixed   $uid
	 * @return \Cartalyst\SentrySocial\Links\LinkInterface
	 */
	public function findLink($slug, $uid)
	{
		$link = $this
			->createModel()
			->newQuery()
			->with('user')
			->where('provider', '=', $slug)
			->where('uid', '=', $uid)
			->first();

		if ($link === null)
		{
			$link = $this->createModel();
			$link->fill(array(
				'provider' => $slug,
				'uid'      => $uid,
			));
			$link->save();
		}

		return $link;
	}

	/**
	 * Create a new instance of the model.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function createModel()
	{
		$class = '\\'.ltrim($this->model, '\\');

		return new $class;
	}

}
