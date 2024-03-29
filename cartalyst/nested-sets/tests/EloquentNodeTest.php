<?php namespace Cartalyst\NestedSets\Tests;
/**
 * Part of the Nested Sets package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Nested Sets
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Mockery as m;
use Cartalyst\NestedSets\Nodes\EloquentNode as Node;
use PHPUnit_Framework_TestCase;

class EloquentNodeTest extends PHPUnit_Framework_TestCase {

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass()
	{
		require_once __DIR__.'/stubs/DummyWorker.php';
	}

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
		Node::unsetPresenter();
	}

	public function testChildrenManipulation()
	{
		$node = new Node;

		$node->setChildren(array('foo'));
		$this->assertCount(1, $node->getChildren());
		$this->assertEquals(array('foo'), $node->getChildren());

		$node->clearChildren();
		$this->assertEmpty($node->getChildren());

		$node->setChildAtIndex($child1 = new Node, 2);
		$this->assertCount(1, $children = $node->getChildren());
		$this->assertEquals($child1, reset($children));
		$this->assertEquals(2, key($children));
	}

	public function testSettingHelper()
	{
		$node = new Node;
		$this->addMockConnection($node);
		$node->setWorker('DummyWorker');
		$this->assertInstanceOf('DummyWorker', $node->createWorker());
	}

	public function testPresenter()
	{
		$this->assertNull(Node::getPresenter());
		Node::setPresenter($presenter = m::mock('Cartalyst\NestedSets\Presenter'));
		$this->assertEquals($presenter, Node::getPresenter());

		$node = new Node;
		$presenter->shouldReceive('presentAs')->with($node, 'foo', 'bar', 0)->once()->andReturn('success');
		$this->assertEquals('success', $node->presentAs('foo', 'bar'));

		$presenter->shouldReceive('presentAs')->with($node, 'baz', 'qux', 2)->once()->andReturn('success');
		$this->assertEquals('success', $node->presentAsBaz('qux', 2));
	}

	public function testFindingChildrenAlwaysReturnsArray()
	{
		$node = m::mock('Cartalyst\NestedSets\Nodes\EloquentNode[createWorker]');
		$node->shouldReceive('createWorker')->once()->andReturn($worker = m::mock('Cartalyst\NestedSets\Workers\WorkerInterface'));
		$worker->shouldReceive('tree')->with($node, 0, null)->once()->andReturn($treeNode = new Node);
		$this->assertEquals(array($treeNode), $node->findChildren());
	}

	protected function addMockConnection($model)
	{
		$model->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));

		$resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
		$model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
		$model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock('Illuminate\Database\Query\Processors\Processor'));
	}

}
