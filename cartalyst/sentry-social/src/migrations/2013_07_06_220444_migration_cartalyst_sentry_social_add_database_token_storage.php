<?php
/**
 * Part of the Sentry Social package.
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
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Database\Migrations\Migration;

class MigrationCartalystSentrySocialAddDatabaseTokenStorage extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('social', function($table)
		{
			// Common
			$table->string('access_token')->nullable();
			$table->integer('end_of_life')->nullable();

			// OAuth2
			$table->string('refresh_token')->nullable();

			// OAuth1
			$table->string('request_token')->nullable();
			$table->string('request_token_secret')->nullable();

			// Misc
			$table->text('extra_params')->nullable();

			$table->unique(array('service', 'access_token'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('social', function($table)
		{
			$table->dropUnique('social_service_access_token_unique');

			$table->dropColumn(array(
				'access_token', 'end_of_life',
				'refresh_token', 'request_token',
				'request_token_secret', 'extra_params',
			));
		});
	}

}
