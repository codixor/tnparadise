## Configuring Sentry Social

Configuring Sentry Social is remarkably easy and varies, depending on how you would like to use it.

### Laravel 4

---

To configure Sentry Social in Laravel 4, you simply need to:

1. Run `php artisan config:publish cartalyst/sentry-social`. This will publish your configuration to `app/config/packages/cartalyst/sentry-social`.
2. Open up the `config.php` configuration. In here, you'll see a number of `connections`. These are the available connections you have with Sentry Social.
3. Run `php artisan migrate --package=cartalyst/sentry-social` so that your database has the latest schema chagnes applied.

For each connection you wish to use, you will need to add your application's `key` and `secret`. You can also add your own connections.

### Everywhere Else

---

> **Note:** We are working towards easier integration with other frameworks in the future with Sentry Social 3, namely FuelPHP and CodeIgniter.

Once you have installed and initialized an instance of `Cartalyst\SentrySocial\Manager`, you need to only call:

	$manager->addConnection('slug', array(
		'driver'     => 'Facebook',
		'identifier' => 'your_app_identifier',
		'secret'     => 'your_app_secret',
		'scopes'     => array(), // OAuth2 only
	));

When you add a connection, you must provide a unique `slug` (which is used when authenticating). You'll also need to pass through an associative array of properties:

1. `driver` - this is the class name of a valid built-in OAuth driver, or a classname of your own custom driver which inherits from a valid base class.
2. `identifier` - this is your application's identifier (also known as an "id" or "key").
3. `secret` - this is your application's secret.
4. `scopes` - with OAuth2 providers, you may provide an optional array of scopes (scopes represent how much data you're requesting).
