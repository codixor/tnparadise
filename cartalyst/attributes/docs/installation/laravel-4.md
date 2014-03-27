# Install & Configure in Laravel 4

> **Note:** To use Cartalyst's Attributes package you need to have a valid Cartalyst.com subscription.
Click [here](https://www.cartalyst.com/pricing) to obtain your subscription.

## Composer {#composer}

Open your `composer.json` file and add the following lines:

	{
		"repositories": [
			{
				"type": "composer",
				"url": "http://packages.cartalyst.com"
			}
		],
		"require": {
			"cartalyst/attributes": "1.0.*",
		},
	}

Run composer update from the command line

	composer update


## Migrations {#migrations}

In order to run the migration successfully, you need to have a default database connection setup on your Laravel 4 application, once you have that setup, you can run the following command:

	php artisan migrate --package=cartalyst/attributes
