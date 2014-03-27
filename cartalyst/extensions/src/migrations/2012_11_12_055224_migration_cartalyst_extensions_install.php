<?php
/**
 * Part of the Extensions package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Extensions
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Database\Migrations\Migration;

class MigrationCartalystExtensionsInstall extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('extensions', function($table)
		{
			$table->string('slug');
			$table->string('version')->nullable();
			$table->boolean('enabled')->default(0);

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$table->primary('slug');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('extensions');
	}

}
