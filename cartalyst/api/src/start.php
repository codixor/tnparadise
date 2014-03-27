<?php
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

// Require this file from bootstrap/start.php by adding
// require_once __DIR__.'/../vendor/cartalyst/api/src/start.php';
// before $app = new Illuminate\Foundation\Application;
// Or just insert this line there.

/*
|--------------------------------------------------------------------------
| Override Request Class
|--------------------------------------------------------------------------
|
| By overriding our request class to that of the API package, we are able
| to inspect input as runtime objects rather than serialized strings.
|
*/

Illuminate\Foundation\Application::requestClass('Cartalyst\Api\Request');
