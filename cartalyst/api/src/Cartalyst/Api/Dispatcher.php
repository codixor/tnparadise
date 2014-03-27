<?php namespace Cartalyst\Api;
/**
 * Part of the API package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    API
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

class Dispatcher {

	/**
	 * The IoC container instance.
	 *
	 * @var \Illuminate\Container\Container
	 */
	protected $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function get($uri)
	{
		return $this->handle('GET', $uri);
	}

	public function delete($uri, $body = null)
	{
		return $this->handle('DELETE', $uri, $body);
	}

	public function put($uri, $body = null)
	{
		return $this->handle('PUT', $uri, $body);
	}

	public function patch($uri, $body = null)
	{
		return $this->handle('PATCH', $uri, $body);
	}

	public function post($uri, $body = null)
	{
		return $this->handle('POST', $uri, $body);
	}

	public function handle($method, $uri, $body = null)
	{
		$parent = $this->container['request'];
		$request = $this->createRequest($method, $uri, $body);

		$this->container->instance('request', $request);
		Facade::clearResolvedInstance('request');

		$response = $this->container['router']->handleSub($request);

		$this->container->instance('request', $parent);
		Facade::clearResolvedInstance('request');

		return $response;
	}

	protected function createRequest($method, $uri, $body = null)
	{
		$parts = parse_url($uri);
		$uri   = $parts['path'];
		$query = array();

		if (isset($parts['query']))
		{
			parse_str($parts['query'], $query);
		}

		$current = $this->container['router']->getCurrentRequest();

		$cookies = $current->cookies->all();
		$files   = $current->files->all();
		$server  = $current->server->all();

		return Request::create($uri, $method, $query, $cookies, $files, $server, $body);
	}

}
