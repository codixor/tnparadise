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

use Illuminate\Http\JsonResponse;

class Response extends JsonResponse {

	/**
	 * {@inheritdoc}
	 */
	public function getData($assoc = false, $depth = 512, $options = 0)
	{
		if (is_string($this->data))
		{
			return parent::getData($assoc, $depth, $options);
		}

		return $this->data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setData($data = array())
	{
		$this->data = $data;
	}

	/**
	 * Transforms live data for a sub request into JSON encoded data.
	 *
	 * @return void
	 */
	public function transform()
	{
		parent::setData($this->data);
	}

}

