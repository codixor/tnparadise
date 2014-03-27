<?php namespace Cartalyst\Workshop;
/**
 * Part of the Workshop package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Workshop
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Workshop\ExtensionCreator;
use Illuminate\Workbench\Console\WorkbenchMakeCommand;

class ExtensionMakeCommand extends WorkbenchMakeCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'extension:create';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new extension';

	/**
	 * Create a new make workbench command instance.
	 *
	 * @param  Cartalyst\Workshop\ExtensionCreator  $creator
	 * @return void
	 */
	public function __construct(ExtensionCreator $creator)
	{
		parent::__construct();

		$this->creator = $creator;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('slug', InputArgument::REQUIRED, 'The slug (vendor/name) of the Extension.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

}
