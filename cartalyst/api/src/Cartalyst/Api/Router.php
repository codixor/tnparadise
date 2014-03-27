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

use Illuminate\Routing\Router as BaseRouter;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Router extends BaseRouter {

	protected $currentRequestType;

	/**
	 * {@inheritDoc}
	 */
	public function handle(SymfonyRequest $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		$this->currentRequestType = $type;

		$response = parent::handle($request, $type, $catch);

		$this->currentRequestType = null;

		return $response;
	}

	/**
	 * Handles a sub request.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handleSub(SymfonyRequest $request)
	{
		$this->disableFilters();

		$response = $this->handle($request, HttpKernelInterface::SUB_REQUEST);

		$this->enableFilters();

		return $response;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function prepareResponse($request, $response)
	{
		if ($this->currentRequestType === HttpKernelInterface::SUB_REQUEST)
		{
			return $response;
		}

		if ($response instanceof Response and ! $response->getContent())
		{
			$response->transform();
		}

		return parent::prepareResponse($request, $response);
	}

}
