<?php namespace Cartalyst\NestedSets;
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

use Cartalyst\NestedSets\Nodes\NodeInterface;
use Closure;

class Presenter {

	/**
	 * The formats in which we can present a node.
	 *
	 * @var array
	 */
	public $formats = array('array', 'ul', 'ol', 'json');

	/**
	 * Presents the node in the given format. If the attribute
	 * provided is a closure, we will call it, providing every
	 * single node recursively. You must return a string from
	 * your closure which will be used as the output for that
	 * node when presenting.
	 *
	 * @param  Cartalyst\NestedSets\Nodes\NodeInterface  $node
	 * @param  string  $format
	 * @param  string|Closure  $attribute
	 * @param  int  $depth
	 * @return mixed
	 */
	public function presentAs(NodeInterface $node, $format, $attribute, $depth = 0)
	{
		return $this->recursivelyPresentAs($node, $format, $attribute, $depth, true);
	}

	/**
	 * Presents the children of the given node in the given
	 * format. If the attribute provided is a closure, we will
	 * call it, providing every single node recursively. You
	 * must return a string from your closure which will be
	 * used as the output for that node when presenting.
	 *
	 * @param  Cartalyst\NestedSets\Nodes\NodeInterface  $node
	 * @param  string  $format
	 * @param  string|Closure  $attribute
	 * @param  int  $depth
	 * @return mixed
	 */
	public function presentChildrenAs(NodeInterface $node, $format, $attribute, $depth = 0)
	{
		return $this->recursivelyPresentAs($node, $format, $attribute, $depth);
	}

	/**
	 * Extracts the presentable data from a node by the given
	 * dynamic attribute.
	 *
	 * @param  Cartalyst\NestedSets\Nodes\NodeInterface  $node
	 * @param  mixed  $attribute
	 * @return mixed
	 */
	public function extractPresentable(NodeInterface $node, $attribute)
	{
		if ($attribute instanceof Closure) return $attribute($node);

		return $node->getAttribute($attribute);
	}

	/**
	 * Presents the given array as an array.
	 *
	 * @param  array  $present
	 * @return array  $present
	 */
	public function presentArrayAsArray(array $present)
	{
		return $present;
	}

	/**
	 * Presents the given array as an unordered HTML list.
	 *
	 * @param  array  $present
	 * @return array  $present
	 */
	public function presentArrayAsUl(array $present)
	{
		return $this->presentArrayAsList($present, 'ul');
	}

	/**
	 * Presents the given array as an ordered HTML list.
	 *
	 * @param  array  $present
	 * @return array  $present
	 */
	public function presentArrayAsOl(array $present)
	{
		return $this->presentArrayAsList($present, 'ol');
	}

	/**
	 * Actually does the magic for presenting nodes.
	 *
	 * @param  Cartalyst\NestedSets\Nodes\NodeInterface  $node
	 * @param  string  $format
	 * @param  string|Closure  $attribute
	 * @param  int  $depth
	 * @return mixed
	 */
	public function recursivelyPresentAs(NodeInterface $node, $format, $attribute, $depth = 0, $includeNode = false)
	{
		$present = array();

		$node->findChildren($depth);

		foreach ($node->getChildren() as $child)
		{
			$extracted = $this->extractPresentable($child, $attribute);

			if ($child->getChildren())
			{
				$present[$extracted] = $this->presentChildrenAs($child, 'array', $attribute, $depth, false);
			}
			else
			{
				$present[] = $extracted;
			}
		}

		if ($includeNode === true)
		{
			$extracted = $this->extractPresentable($node, $attribute);

			$present = array($extracted => $present);
		}

		return $this->{'presentArrayAs'.ucfirst($format)}($present);
	}

	/**
	 * Presents the given array as the given HTML list.
	 *
	 * @param  array   $present
	 * @param  string  $type
	 * @return array   $present
	 */
	protected function presentArrayAsList(array $present, $type = 'ul')
	{
		$html = '';

		if (count($present) == 0) return $html;

		foreach ($present as $key => $value)
		{
			// If the value is an array, we will recurse the function so that we can
			// produce a nested list within the list being built. Of course, nested
			// lists may exist within nested lists, etc.
			if (is_array($value))
			{
				if (is_int($key))
				{
					$html .= $this->presentArrayAsList($value, $type);
				}
				else
				{
					$html .= '<li>'.$key.$this->presentArrayAsList($value, $type).'</li>';
				}
			}
			else
			{
				$html .= '<li>'.$value.'</li>';
			}
		}

		return '<'.$type.'>'.$html.'</'.$type.'>';
	}

}
