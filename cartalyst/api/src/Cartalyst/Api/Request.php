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

use Illuminate\Http\Request as BaseRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

class Request extends BaseRequest {

	/**
	 * {@inheritDoc}
	 */
	public function json($key = null, $default = null)
	{
		if ( ! isset($this->json))
		{
			$json = $this->getContent();

			if ( ! is_array($json))
			{
				$json = (array) json_decode($json, true);
			}

			$this->json = new ParameterBag($json);
		}

		return parent::json($key, $default);
	}

}
