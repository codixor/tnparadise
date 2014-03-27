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
use Cartalyst\SentrySocial\Links\Eloquent\Provider;
use PHPUnit_Framework_TestCase;

class EloquentLinkProviderTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testFindingExistingLink()
	{
		$linkProvider = m::mock('Cartalyst\SentrySocial\Links\Eloquent\Provider[createModel]');
		$linkProvider->shouldReceive('createModel')->once()->andReturn($query = m::mock('stdClass'));
		$query->shouldReceive('newQuery')->once()->andReturn($query);
		$query->shouldReceive('where')->with('provider', '=', 'slug')->once()->andReturn($query);
		$query->shouldReceive('where')->with('uid', '=', 789)->once()->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn('success');

		$this->assertEquals('success', $linkProvider->findLink('slug', 789));
	}

	public function testFindingNonExistentLink()
	{
		$linkProvider = m::mock('Cartalyst\SentrySocial\Links\Eloquent\Provider[createModel]');

		$linkProvider->shouldReceive('createModel')->ordered()->once()->andReturn($query = m::mock('stdClass'));
		$query->shouldReceive('newQuery')->once()->andReturn($query);
		$query->shouldReceive('where')->with('provider', '=', 'slug')->once()->andReturn($query);
		$query->shouldReceive('where')->with('uid', '=', 789)->once()->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn(null);

		$linkProvider->shouldReceive('createModel')->ordered()->once()->andReturn($model = m::mock('stdClass')); // Can't mock model, get "BadMethodCallException: Method Cartalyst\SentrySocial\Links\Eloquent\Link::hasGetMutator() does not exist on this mock object"
		$model->shouldReceive('fill')->with(array(
			'provider' => 'slug',
			'uid'      => 789,
		))->once();
		$model->shouldReceive('save')->once();

		$this->assertEquals($model, $linkProvider->findLink('slug', 789));
	}

	public function testCreateModel()
	{
		$provider = new Provider;
		$model = $provider->createModel();
		$this->assertInstanceOf('Cartalyst\SentrySocial\Links\Eloquent\Link', $model);
	}

}
