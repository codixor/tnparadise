<?php namespace Cartalyst\Sentry\Reminders;
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

use Illuminate\Database\Eloquent\Model;

class EloquentReminder extends Model {

	/**
	 * {@inheritDoc}
	 */
	protected $table = 'reminders';

	/**
	 * {@inheritDoc}
	 */
	protected $fillable = array(
		'code',
		'completed',
		'completed_at',
	);

	/**
	 * Get mutator for the "completed" attribute.
	 *
	 * @param  mixed  $completed
	 * @return bool
	 */
	public function getCompletedAttribute($completed)
	{
		return (bool) $completed;
	}

	/**
	 * Set mutator for the "completed" attribute.
	 *
	 * @param  mixed  $completed
	 * @return void
	 */
	public function setCompletedAttribute($completed)
	{
		$this->attributes['completed'] = (int) (bool) $completed;
	}

}
